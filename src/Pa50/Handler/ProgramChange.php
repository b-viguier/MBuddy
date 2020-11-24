<?php

namespace bviguier\MBuddy\Pa50\Handler;

use bviguier\MBuddy\Handler;
use bviguier\MBuddy\MidiSyxBank;
use bviguier\RtMidi;

class ProgramChange implements Handler
{
    public function __construct(MidiSyxBank $syxBank, RtMidi\Output $device)
    {
        $this->syxBank = $syxBank;
        $this->device = $device;
    }

    public function statusByte(): ?int
    {
        // PC on channel 16
        return 0xCF;
    }

    public function handle(RtMidi\Message $message): ?RtMidi\Message
    {
        assert($message->byte(0) === $this->statusByte());
        assert($message->size() === 2);

        if(!$this->enabled) {
            return $message;
        }

        if($data = $this->syxBank->load($message->byte(1)))
        {
            $this->device->send(RtMidi\Message::fromBinString($data));
        }

        return null;
    }

    public function enable(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    private bool $enabled = false;
    private MidiSyxBank $syxBank;
    private RtMidi\Output $device;
}
