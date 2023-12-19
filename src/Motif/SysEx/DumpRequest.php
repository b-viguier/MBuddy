<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

class DumpRequest implements \Stringable
{
    private const SYSEX_HEADER = [0XF0, 0x43, 0x20, 0x7F, 0x03];
    private const SYSEX_FOOTER = 0xF7;
    private string $binaryString;

    public function __construct(private Address $address)
    {
        $this->binaryString = pack(
            'C*',
            ...[
                ...self::SYSEX_HEADER,
                ...$address->toArray(),
                self::SYSEX_FOOTER,
            ]
        );
    }

    public function __toString(): string
    {
        return $this->binaryString;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}
