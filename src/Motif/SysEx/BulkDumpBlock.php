<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

use Bveing\MBuddy\Motif\Sysex;

class BulkDumpBlock
{
    public const DEVICE_NUMBER = 0x00;

    private const OFFSET_BYTE_COUNT_MSB = 0;
    private const OFFSET_BYTE_COUNT_LSB = 1;
    private const OFFSET_ADDRESS = 2;
    private const OFFSET_DATA = 5;
    private const MIN_FIXED_SIZE = 6;
    private const HEADER_BLOCK_ID = 0x0E;
    private const FOOTER_BLOCK_ID = 0x0F;


    static public function create(
        int $byteCount,
        Address $address,
        array $data,
    ): self {
        assert($byteCount === count($data));
        assert(array_keys($data) === range(0, $byteCount - 1));

        return new self($address, $data);
    }

    static public function createHeaderBlock(
        int $addressM,
        int $addressL,
    ): self {
        return new self(new Address(self::HEADER_BLOCK_ID, $addressM, $addressL), []);
    }

    public function isHeaderBlock(): bool
    {
        return $this->address->h() === self::HEADER_BLOCK_ID;
    }

    static public function createFooterBlock(
        int $addressM,
        int $addressL,
    ): self {
        return new self(new Address(self::FOOTER_BLOCK_ID, $addressM, $addressL), []);
    }

    public function isFooterBlock(): bool
    {
        return $this->address->h() === self::FOOTER_BLOCK_ID;
    }

    static public function fromSysex(Sysex $sysex): self
    {
        if ($sysex->getDeviceNumber() !== self::DEVICE_NUMBER) {
            throw new \InvalidArgumentException('Invalid Device Number');
        }

        $bytes = array_values(unpack('C*', $sysex->getData()));

        if (count($bytes) < self::MIN_FIXED_SIZE) {
            throw new \InvalidArgumentException('Invalid BulkDump size');
        }

        $dataByteCount = $bytes[self::OFFSET_BYTE_COUNT_MSB] * 128 + $bytes[self::OFFSET_BYTE_COUNT_LSB];
        if ($dataByteCount + self::MIN_FIXED_SIZE != count($bytes)) {
            throw new \InvalidArgumentException('Wrong BulkDump size');
        }

        $checkSumData = array_slice($bytes, self::OFFSET_BYTE_COUNT_MSB, -1);
        $checksum = $bytes[self::OFFSET_DATA + $dataByteCount];
        if ((array_sum($checkSumData) + $checksum) % 128 !== 0) {
            throw new \InvalidArgumentException('Invalid Checksum');
        }

        $data = array_slice($bytes, self::OFFSET_DATA, $dataByteCount);
        $address = array_slice($bytes, self::OFFSET_ADDRESS, 3);

        return new self(new Address(...$address), $data);
    }

    private function __construct(
        private Address $address,
        private array $data,
    ) {
        assert(array_reduce($data, fn($carry, $item) => $carry && is_int($item), true));
    }

    public function toSysex(): Sysex
    {
        $byteCount = count($this->data);
        $msg = [
            intdiv($byteCount, 128),
            $byteCount % 128,
            ...$this->address->toArray(),
            ...$this->data,
        ];

        $checksum = 128 - (array_sum($msg) % 128);

        return Sysex::fromBinaryString(
            pack('C*', ...[
                ...$msg,
                $checksum,
            ]),
        );
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
