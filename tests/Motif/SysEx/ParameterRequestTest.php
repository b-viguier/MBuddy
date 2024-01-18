<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Motif\SysEx\ParameterRequest;
use Bveing\MBuddy\Motif\SysEx\Address;

class ParameterRequestTest extends TestCase
{
    public function testValues(): void
    {
        $address = new Address(0x01, 0x02, 0x03);
        $parameterRequest = new ParameterRequest($address);
        $sysex = $parameterRequest->toSysex();

        self::assertSame($address, $parameterRequest->getAddress());
        self::assertSame(ParameterRequest::DEVICE_NUMBER, $sysex->getDeviceNumber());
        self::assertSame($address->toArray(), $sysex->getBytes());
    }
}
