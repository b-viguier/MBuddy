<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

use Bveing\MBuddy\Motif\Sysex;

class DumpRequest
{
    public const DEVICE_NUMBER = 0x20;
    private Sysex $sysex;

    public function __construct(private Address $address)
    {
        $this->sysex = Sysex::fromData(self::DEVICE_NUMBER, $this->address->toBinaryString());
    }

    public function toSysex(): Sysex
    {
        return $this->sysex;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}
