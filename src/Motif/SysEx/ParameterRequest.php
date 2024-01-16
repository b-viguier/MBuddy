<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

use Bveing\MBuddy\Motif\SysEx;

class ParameterRequest
{
    public const DEVICE_NUMBER = 0x30;
    private SysEx $sysex;

    public function __construct(private Address $address)
    {
        $this->sysex = SysEx::fromData(
            self::DEVICE_NUMBER,
            $address->toBinaryString(),
        );
    }

    public function toSysex(): SysEx
    {
        return $this->sysex;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}
