<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Motif\SysEx\Address;

class AddressTest extends TestCase
{
    public function testValuesAccessors(): void
    {
        $address = new Address(
            $h = 0x01,
            $m = 0x02,
            $l = 0x03,
        );

        self::assertSame($h, $address->h());
        self::assertSame($m, $address->m());
        self::assertSame($l, $address->l());
    }

    public function testArrayConversion(): void
    {
        $address = new Address(
            $h = 0x01,
            $m = 0x02,
            $l = 0x03,
        );

        self::assertSame([$h, $m, $l], $address->toArray());
    }

    public function testBinaryStringConversion(): void
    {
        $address = new Address(
            ord('A'),
            ord('B'),
            ord('C'),
        );

        self::assertSame('ABC', $address->toBinaryString());
    }

    public function testEquality(): void
    {
        $address = new Address(
            $h = 0x01,
            $m = 0x02,
            $l = 0x03,
        );

        self::assertTrue($address->equals(new Address($h, $m, $l)));
        self::assertFalse($address->equals(new Address($h + 1, $m, $l)));
        self::assertFalse($address->equals(new Address($h, $m + 1, $l)));
        self::assertFalse($address->equals(new Address($h, $m, $l + 1)));
    }
}
