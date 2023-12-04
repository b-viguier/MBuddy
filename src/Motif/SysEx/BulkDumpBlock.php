<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

class BulkDumpBlock implements \Stringable
{
    private const SYSEX_HEADER = [0xF0, 0x43, 0x00, 0x7F, 0x03];
    private const SYSEX_FOOTER = 0xF7;

    private const OFFSET_BYTE_COUNT_MSB = 5;
    private const OFFSET_BYTE_COUNT_LSB = 6;
    private const OFFSET_ADDRESS = 7;
    private const OFFSET_DATA = 10;

    private const MIN_FIXED_SIZE = 12;


    static public function create(
        int $byteCount,
        Address $address,
        array $data,
    ): self {
        assert($byteCount === count($data));

        return new self($address, $data);
    }

    static public function fomBinaryString(string $binaryString): self
    {
        $bytes = unpack('C*', $binaryString);

        if (count($bytes) < self::MIN_FIXED_SIZE) {
            throw new \InvalidArgumentException('Invalid BulkDump size');
        }

        if (
            self::SYSEX_HEADER != array_slice($bytes, 0, count(self::SYSEX_HEADER))
            || self::SYSEX_FOOTER != end($bytes)
        ) {
            throw new \InvalidArgumentException('Invalid SysEx message');
        }

        $dataByteCount = $bytes[self::OFFSET_BYTE_COUNT_MSB] * 128 + $bytes[self::OFFSET_BYTE_COUNT_LSB];
        if ($dataByteCount + self::MIN_FIXED_SIZE != count($bytes)) {
            throw new \InvalidArgumentException('Wrong BulkDump size');
        }

        $data = array_slice($bytes, self::OFFSET_DATA, $dataByteCount);
        $checksum = $bytes[self::OFFSET_DATA + $dataByteCount];
        if ((array_sum($data) + $checksum) % 128 !== 0) {
            throw new \InvalidArgumentException('Invalid Checksum');
        }

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
        $byteCount = count($this->data);
        $msg = [
            intdiv($byteCount, 128),
            $byteCount % 128,
            ...$this->address->toArray(),
            ...$this->data,
        ];

        $checksum = 128 - (array_sum($msg) % 128);

        return pack('C*', ...[
            ...self::SYSEX_HEADER,
            ...$msg,
            $checksum,
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