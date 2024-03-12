<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use Bveing\MBuddy\Motif\SysEx\Address;
use Bveing\MBuddy\Motif\SysEx\DumpRequest;
use PHPUnit\Framework\TestCase;

class DumpRequestTest extends TestCase
{
    public function testValues(): void
    {
        $address = new Address(0x01, 0x02, 0x03);
        $dumpRequest = new DumpRequest($address);
        $sysex = $dumpRequest->toSysEx();

        self::assertSame($address, $dumpRequest->address());
        self::assertSame(DumpRequest::DEVICE_NUMBER, $sysex->deviceNumber());
        self::assertSame($address->toArray(), $sysex->toBytes());
    }
}
