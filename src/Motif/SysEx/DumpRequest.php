<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

use Bveing\MBuddy\Motif\SysEx;

class DumpRequest
{
    public const DEVICE_NUMBER = 0x20;

    public function __construct(private Address $address)
    {
        $this->sysex = SysEx::fromBytes(self::DEVICE_NUMBER, $this->address->toBinaryString());
    }

    public function toSysex(): SysEx
    {
        return $this->sysex;
    }

    public function address(): Address
    {
        return $this->address;
    }
    private SysEx $sysex;
}
