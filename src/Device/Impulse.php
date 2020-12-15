<?php

namespace bviguier\MBuddy\Device;

use bviguier\MBuddy\Device;
use bviguier\MBuddy\MidiSyxBank;
use bviguier\MBuddy\Preset;
use bviguier\RtMidi;
use Psr\Log\LoggerInterface;

class Impulse implements Device
{
    public const BANK_MSB = 1;
    public const BANK_LSB = 0;

    public function __construct(RtMidi\Input $input, RtMidi\Output $output, MidiSyxBank $syxBank, LoggerInterface $logger)
    {
        $this->input = $input;
        $this->output = $output;
        $this->bank = $syxBank;
        $this->onPresetSaved = function(Preset $preset): void {};
        $this->onMidiEvent = function(RtMidi\Message $msg): void {};
        $this->logger = $logger;

        $this->input->allow(RtMidi\Input::ALLOW_SYSEX);
    }

    /**
     * @return callable(Preset): void
     */
    public function doLoadPreset(): callable
    {
        return function (Preset $preset): void {
            if ($preset->bankMSB() !== self::BANK_MSB || $preset->bankLSB() !== self::BANK_LSB) {
                return;
            }

            if ($data = $this->bank->load($preset->program())) {
                $patch = Device\Impulse\Patch::fromBinString($data);
                $this->logger->notice("Preset loaded ($preset}) '{$patch->name()}'.");
                $this->output->send($patch->toSysexMessage());
            }
        };
    }

    /**
     * @param callable(Preset):void $callback
     */
    public function onPresetSaved(callable $callback): void
    {
        $this->onPresetSaved = $callback;
    }

    /**
     * @param callable(RtMidi\Message):void $callback
     */
    public function onMidiEvent(callable $callback): void
    {
        $this->onMidiEvent = $callback;
    }

    public function process(int $limit): int
    {
        for ($count = 0; $count < $limit && $msg = $this->input->pullMessage(); ++$count) {
            // Sysex handling
            if ($msg->byte(0) === 0xF0) {
                $patch = Device\Impulse\Patch::fromSysexMessage($msg);

                if(null !== $prgId = $this->bank->save($patch->name(), $patch->toBinString())) {
                    $preset = new Preset(self::BANK_MSB, self::BANK_LSB, $prgId);
                    $this->logger->notice("Preset saved ($preset) '{$patch->name()}'.");
                    ($this->onPresetSaved)($preset);
                }

            } else {
                ($this->onMidiEvent)($msg);
            }
        }

        return $count;
    }

    private RtMidi\Input $input;
    private RtMidi\Output $output;
    private MidiSyxBank $bank;
    /** @var callable(Preset): void */
    private $onPresetSaved;
    /** @var callable(RtMidi\Message): void */
    private $onMidiEvent;
    private LoggerInterface $logger;

}
