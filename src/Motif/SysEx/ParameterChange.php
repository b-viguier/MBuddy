<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

use Bveing\MBuddy\Motif\Sysex;

class ParameterChange
{
    public const DEVICE_NUMBER = 0x10;
    private const OFFSET_ADDRESS = 0;
    private const OFFSET_DATA = 3;

    private const MIN_FIXED_SIZE = 4;


    public static function create(
        Address $address,
        array $data,
    ): self {
        return new self($address, $data);
    }

    public static function fromSysex(Sysex $sysex): self
    {
        if ($sysex->getDeviceNumber() !== self::DEVICE_NUMBER) {
            throw new \InvalidArgumentException('Invalid Device Number');
        }

        $bytes = array_values(unpack('C*', $sysex->getData()));

        if (count($bytes) < self::MIN_FIXED_SIZE) {
            throw new \InvalidArgumentException('Invalid BulkDump size');
        }

        $data = array_slice($bytes, self::OFFSET_DATA);
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
        return Sysex::fromData(
            self::DEVICE_NUMBER,
            pack(
                'C*',
                ...$this->address->toArray(),
                ...$this->data,
            ),
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
