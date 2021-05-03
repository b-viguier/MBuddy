<?php

namespace bviguier\MBuddy\Device\Impulse;

use bviguier\MBuddy\SongId;
use bviguier\RtMidi;

class Patch
{
    public const LENGTH = 288;

    static public function fromBinString(string $data): ?self
    {
        $instance = new self($data);

        // Check if an ID is available in name
        $songId = substr($data, self::FULLNAME_OFFSET, self::ID_LENGTH);
        if (!ctype_digit($songId)) {
            return null;
        }

        return $instance;
    }

    static public function fromSysexMessage(RtMidi\Message $message): ?self
    {
        return self::fromBinString($message->toBinString());
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
        return trim(substr($this->fullname(), self::ID_LENGTH));
    }

    public function songId(): SongId
    {
        return new SongId((int)substr($this->fullname(), 0, self::ID_LENGTH));
    }

    public function fullname(): string
    {
        return substr($this->data, self::FULLNAME_OFFSET, self::FULLNAME_LENGTH);
    }

    public function withName(string $name): self
    {
        $name = str_pad(substr($name, 0, self::NAME_LENGTH), self::NAME_LENGTH);

        return new self(substr_replace($this->data, $name, self::FULLNAME_OFFSET + self::ID_LENGTH, self::NAME_LENGTH));
    }

    public function withId(int $id): self
    {
        $id = str_pad((string)($id % (10 ** self::ID_LENGTH)), self::ID_LENGTH, '0', STR_PAD_LEFT);

        return new self(substr_replace($this->data, $id, self::FULLNAME_OFFSET, self::ID_LENGTH));
    }

    private function __construct(string $data)
    {
        if (strlen($data) !== self::LENGTH) throw new PatchCorruption('Invalid Impulse Patch');
        if (ord($data[0]) !== 0xF0 || ord($data[self::LENGTH - 1]) !== 0xF7) throw new PatchCorruption('Invalid Sysex');
        if (ord($data[1]) !== 0x00 || ord($data[2]) !== 0x20 || ord($data[3]) !== 0x29) throw new PatchCorruption('Invalid Novation ID');
        if (ord($data[4]) !== 0x43 || ord($data[5]) !== 0x00 || ord($data[6]) !== 0x00) throw new PatchCorruption('Invalid Impulse ID');

        $this->data = $data;
    }

    private const FULLNAME_OFFSET = 7;
    private const FULLNAME_LENGTH = 8;
    private const ID_LENGTH = 2;
    private const NAME_LENGTH = self::FULLNAME_LENGTH - self::ID_LENGTH;

    private string $data;
}
