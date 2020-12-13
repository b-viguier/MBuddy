<?php

namespace bviguier\MBuddy\Device\Impulse;

use bviguier\RtMidi;

class Patch
{
    public const LENGTH = 288;

    static public function fromBinString(string $data): self
    {
        return new self($data);
    }

    static public function fromSysexMessage(RtMidi\Message $message): self
    {
        return new self($message->toBinString());
    }

    public function toBinString(): string
    {
        return $this->data;
    }

    public function toSysexMessage(): RtMidi\Message
    {
        return RtMidi\Message::fromBinString($this->data);
    }

    public function name(): string
    {
        return trim(substr($this->data, self::NAME_OFFSET, self::NAME_LENGTH));
    }

    public function withName(string $name): self
    {
        $name = str_pad(substr($name, 0, self::NAME_LENGTH), self::NAME_LENGTH);

        return new self(substr_replace($this->data, $name, self::NAME_OFFSET, self::NAME_LENGTH));
    }

    private function __construct(string $data)
    {
        if (strlen($data) !== self::LENGTH) throw new PatchCorruption('Invalid Impulse Patch');
        if (ord($data[0]) !== 0xF0 || ord($data[self::LENGTH-1]) !== 0xF7) throw new PatchCorruption('Invalid Sysex');
        if (ord($data[1]) !== 0x00 || ord($data[2]) !== 0x20 || ord($data[3]) !== 0x29) throw new PatchCorruption('Invalid Novation ID');
        if (ord($data[4]) !== 0x43 || ord($data[5]) !== 0x00 || ord($data[6]) !== 0x00) throw new PatchCorruption('Invalid Impulse ID');

        $this->data = $data;
    }

    private const NAME_OFFSET = 7;
    private const NAME_LENGTH = 8;

    private string $data;
}
