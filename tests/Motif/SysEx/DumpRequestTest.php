<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Motif\SysEx\DumpRequest;
use Bveing\MBuddy\Motif\SysEx\Address;

class DumpRequestTest extends TestCase
{
    public function testValues(): void
    {
        $address = new Address(0x01, 0x02, 0x03);
        $dumpRequest = new DumpRequest($address);
        $sysex = $dumpRequest->toSysex();

        self::assertSame($address, $dumpRequest->getAddress());
        self::assertSame(DumpRequest::DEVICE_NUMBER, $sysex->getDeviceNumber());
        self::assertSame($address->toArray(), $sysex->getBytes());
    }
}
