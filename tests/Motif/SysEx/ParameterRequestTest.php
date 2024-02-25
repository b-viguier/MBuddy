<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use Bveing\MBuddy\Motif\SysEx\Address;
use Bveing\MBuddy\Motif\SysEx\ParameterRequest;
use PHPUnit\Framework\TestCase;

class ParameterRequestTest extends TestCase
{
    public function testValues(): void
    {
        $address = new Address(0x01, 0x02, 0x03);
        $parameterRequest = new ParameterRequest($address);
        $sysex = $parameterRequest->toSysex();

        self::assertSame($address, $parameterRequest->address());
        self::assertSame(ParameterRequest::DEVICE_NUMBER, $sysex->deviceNumber());
        self::assertSame($address->toArray(), $sysex->toBytes());
    }
}
