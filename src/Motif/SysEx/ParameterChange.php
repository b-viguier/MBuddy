<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

class ParameterChange implements \Stringable
{
    private const SYSEX_HEADER = [0xF0, 0x43, 0x10, 0x7F, 0x03];
    private const SYSEX_FOOTER = 0xF7;

    private const OFFSET_ADDRESS = 5;
    private const OFFSET_DATA = 8;

    private const MIN_FIXED_SIZE = 10;


    static public function create(
        Address $address,
        array $data,
    ): self {
        return new self($address, $data);
    }

    static public function fomBinaryString(string $binaryString): self
    {
        $bytes = array_values(unpack('C*', $binaryString));

        if (count($bytes) < self::MIN_FIXED_SIZE) {
            throw new \InvalidArgumentException('Invalid BulkDump size');
        }

        if (
            self::SYSEX_HEADER != array_slice($bytes, 0, count(self::SYSEX_HEADER))
            || self::SYSEX_FOOTER != end($bytes)
        ) {
            throw new \InvalidArgumentException('Invalid SysEx message');
        }

        $data = array_slice($bytes, self::OFFSET_DATA, -1);
        $address = array_slice($bytes, self::OFFSET_ADDRESS, 3);

        return new self(new Address(...$address), $data);
    }

    private function __construct(
        private Address $address,
        private array $data,
    ) {
        assert(array_reduce($data, fn($carry, $item) => $carry && is_int($item), true));
    }

    public function __toString(): string
    {
        return pack('C*', ...[
            ...self::SYSEX_HEADER,
            ...$this->address->toArray(),
            ...$this->data,
            self::SYSEX_FOOTER,
        ]);
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
