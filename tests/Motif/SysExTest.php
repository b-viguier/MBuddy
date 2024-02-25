<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif;

use Bveing\MBuddy\Motif\SysEx;
use PHPUnit\Framework\TestCase;

class SysExTest extends TestCase
{
    /**
     * @dataProvider invalidBinaryStringProvider
     */
    public function testInvalidBinaryString(string $binaryString): void
    {
        self::assertNull(SysEx::fromBinaryString($binaryString));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function invalidBinaryStringProvider(): iterable
    {
        yield 'too short' => ['ABC'];
        yield 'wrong prefix' => ['AAAAAA'];
        yield 'wrong suffix' => ["\xF0\x43____"];
        yield 'wrong model ID' => ["\xF0\x43___\x7F"];
    }

    public function testFromBinaryString(): void
    {
        $binaryString = "\xF0\x43*\x7F\x03data\xF7";
        $sysex = SysEx::fromBinaryString($binaryString);

        self::assertNotNull($sysex);
        self::assertSame($binaryString, (string)$sysex);
        self::assertSame(ord('*'), $sysex->deviceNumber());
        self::assertSame([ord('d'), ord('a'), ord('t'), ord('a')], $sysex->toBytes());
    }

    public function testFromBytes(): void
    {
        $sysex = SysEx::fromBytes(
            $deviceNumber = 0x01,
            $data = 'data',
        );

        self::assertSame("\xF0\x43\x01\x7F\x03data\xF7", (string)$sysex);
        self::assertSame($deviceNumber, $sysex->deviceNumber());
        self::assertSame([ord('d'), ord('a'), ord('t'), ord('a')], $sysex->toBytes());
    }
}
