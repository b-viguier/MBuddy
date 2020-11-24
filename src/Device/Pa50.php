<?php

namespace bviguier\MBuddy\Device;

use bviguier\MBuddy\Device;
use bviguier\MBuddy\Preset;
use bviguier\RtMidi;

class Pa50 implements Device
{
    public function __construct(RtMidi\Input $input, RtMidi\Output $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->currentPreset = new Preset(0, 0, 0);
        $this->onExternalPresetLoaded = function(Preset $p): void {};
    }

    /**
     * @param callable(Preset): void $callback
     */
    public function onExternalPresetLoaded(callable $callback): void
    {
        $this->onExternalPresetLoaded = $callback;
    }

    /**
     * @return callable(Preset):void
     */
    public function doSaveExternalPreset(): callable
    {
        return function (Preset $preset): void {
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_CC | self::CHANNEL_16, self::CC_BANK_MSB, $preset->bankMSB()
            ));
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_CC | self::CHANNEL_16, self::CC_BANK_MSB, $preset->bankLSB()
            ));
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_PC | self::CHANNEL_16, $preset->program()
            ));
        };
    }

    /**
     * @return callable(RtMidi\Message):void
     */
    public function doPlayEvent(): callable
    {
        return [$this->output, 'send'];
    }

    public function process(int $limit): int
    {
        for ($count = 0; $count < $limit && $msg = $this->input->pullMessage(); ++$count) {
            switch ($msg->byte(0)) {
                case self::STATUS_CC | self::CHANNEL_16:
                    switch ($msg->byte(1)) {
                        case self::CC_BANK_MSB:
                            $this->currentPreset = new Preset(
                                $msg->byte(2),
                                $this->currentPreset->bankLSB(),
                                $this->currentPreset->program(),
                            );
                            ($this->onExternalPresetLoaded)($this->currentPreset);
                            break;
                        case self::CC_BANK_LSB:
                            $this->currentPreset = new Preset(
                                $this->currentPreset->bankMSB(),
                                $msg->byte(2),
                                $this->currentPreset->program(),
                            );
                            ($this->onExternalPresetLoaded)($this->currentPreset);
                            break;
                    }
                    break;
                case self::STATUS_PC | self::CHANNEL_16:
                    $this->currentPreset = new Preset(
                        $this->currentPreset->bankMSB(),
                        $this->currentPreset->bankLSB(),
                        $msg->byte(1),
                    );
                    ($this->onExternalPresetLoaded)($this->currentPreset);
                    break;
            }
        }

        return $count;
    }

    private RtMidi\Input $input;
    private RtMidi\Output $output;
    private Preset $currentPreset;
    /** @var callable(Preset): void */
    private $onExternalPresetLoaded;

    private const STATUS_CC = 0xB0;
    private const STATUS_PC = 0xC0;
    private const CHANNEL_16 = 0x0F;
    private const CC_BANK_MSB = 0x00;
    private const CC_BANK_LSB = 0x20;
}
