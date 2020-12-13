<?php

namespace bviguier\MBuddy\Device;

use bviguier\MBuddy\Device;
use bviguier\MBuddy\Preset;
use bviguier\RtMidi;
use Psr\Log\LoggerInterface;

class Pa50 implements Device
{
    public function __construct(RtMidi\Input $input, RtMidi\Output $output, LoggerInterface $logger)
    {
        $this->input = $input;
        $this->output = $output;
        $this->currentMSB = 0;
        $this->currentLSB = 0;
        $this->onExternalPresetLoaded = function(Preset $p): void {};
        $this->logger = $logger;
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
                self::STATUS_CC | self::CHANNEL_16, self::CC_BANK_LSB, $preset->bankLSB()
            ));
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_PC | self::CHANNEL_16, $preset->program()
            ));
            $this->logger->notice("External Preset saved ($preset)");
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
                            $this->currentMSB = $msg->byte(2);
                            break;
                        case self::CC_BANK_LSB:
                            $this->currentLSB = $msg->byte(2);
                            break;
                    }
                    break;
                case self::STATUS_PC | self::CHANNEL_16:
                    $preset = new Preset(
                        $this->currentMSB,
                        $this->currentLSB,
                        $msg->byte(1),
                    );
                    $this->logger->notice("External Preset changed ($preset)");
                    ($this->onExternalPresetLoaded)($preset);
                    break;
            }
        }

        return $count;
    }

    private RtMidi\Input $input;
    private RtMidi\Output $output;
    private int $currentMSB;
    private int $currentLSB;
    /** @var callable(Preset): void */
    private $onExternalPresetLoaded;
    private LoggerInterface $logger;

    private const STATUS_CC = 0xB0;
    private const STATUS_PC = 0xC0;
    private const CHANNEL_16 = 0x0F;
    private const CC_BANK_MSB = 0x00;
    private const CC_BANK_LSB = 0x20;
}
