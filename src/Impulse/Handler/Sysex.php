<?php

namespace bviguier\MBuddy\Impulse\Handler;

use bviguier\MBuddy\Handler;
use bviguier\MBuddy\MidiSyxBank;
use bviguier\RtMidi;

class Sysex implements Handler
{
    public function __construct(MidiSyxBank $syxBank, RtMidi\Output $device)
    {
        $this->syxBank = $syxBank;
        $this->device = $device;
    }

    public function statusByte(): ?int
    {
        return 0xF0;
    }

    public function handle(RtMidi\Message $message): ?RtMidi\Message
    {
        assert($message->byte(0) === $this->statusByte());

        $data = $message->toBinString();
        $name = trim(substr($data, 7, 8));
        $prgId = $this->syxBank->save($name, $data);

        $this->device->send(RtMidi\Message::fromIntegers(0xBF,0x00, 0));
        $this->device->send(RtMidi\Message::fromIntegers(0xBF,0x20, 0));
        $this->device->send(RtMidi\Message::fromIntegers(0xCF, $prgId));

        return null;
    }

    private MidiSyxBank $syxBank;
    private RtMidi\Output $device;

}
