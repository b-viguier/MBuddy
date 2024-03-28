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
        self::assertSame(\strlen($binaryString), $sysex->length());
        self::assertSame(\ord('*'), $sysex->deviceNumber());
        self::assertSame([\ord('d'), \ord('a'), \ord('t'), \ord('a')], $sysex->toBytes());
    }

    public function testFromBytes(): void
    {
        $sysex = SysEx::fromBytes(
            $deviceNumber = 0x01,
            $data = 'data',
        );

        self::assertSame("\xF0\x43\x01\x7F\x03data\xF7", (string)$sysex);
        self::assertSame($deviceNumber, $sysex->deviceNumber());
        self::assertSame([\ord('d'), \ord('a'), \ord('t'), \ord('a')], $sysex->toBytes());
    }

    public function testExtractFromBinaryString(): void
    {
        $binary = \join('', \array_map(
            fn(SysEx $sysEx) => (string)$sysEx,
            [
                $sysEx1 = SysEx::fromBytes(0x01, 'number1'),
                $sysEx2 = SysEx::fromBytes(0x02, 'this is 2'),
            ],
        ));

        $sysEx = SysEx::extractFromBinaryString($binary, 0);
        self::assertNotNull($sysEx);
        self::assertSame((string)$sysEx1, (string)$sysEx);

        $sysEx = SysEx::extractFromBinaryString($binary, \strlen((string)$sysEx));
        self::assertNotNull($sysEx);
        self::assertSame((string)$sysEx2, (string)$sysEx);

        self::assertNull(SysEx::extractFromBinaryString($binary, 1));
    }
}
