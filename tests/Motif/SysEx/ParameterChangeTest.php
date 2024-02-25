<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use Bveing\MBuddy\Motif\SysEx;
use Bveing\MBuddy\Motif\SysEx\Address;
use Bveing\MBuddy\Motif\SysEx\ParameterChange;
use PHPUnit\Framework\TestCase;

class ParameterChangeTest extends TestCase
{
    public function testValues(): void
    {
        $address = new Address(1, 2, 3);
        $data = [1, 2, 3, 4];

        $parameterChange = ParameterChange::create($address, $data);
        $sysex = $parameterChange->toSysex();

        self::assertSame($address, $parameterChange->address());
        self::assertSame($data, $parameterChange->data());
        self::assertSame(ParameterChange::DEVICE_NUMBER, $sysex->deviceNumber());
        self::assertSame([...$address->toArray(), ...$data], $sysex->toBytes());
    }

    /**
     * @dataProvider invalidSysExProvider
     */
    public function testFromInvalidSysEx(SysEx $sysEx, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        ParameterChange::fromSysex($sysEx);
    }

    /**
     * @return iterable<string, array{0: SysEx, 1: string}>
     */
    public static function invalidSysExProvider(): iterable
    {
        yield 'invalid device number' => [
            SysEx::fromBytes(ParameterChange::DEVICE_NUMBER + 1, '*'),
            'Invalid Device Number',
        ];
        yield 'invalid size' => [
            SysEx::fromBytes(ParameterChange::DEVICE_NUMBER, '*'),
            'Invalid BulkDump size',
        ];
    }

    public function testFromSysEx(): void
    {
        $address = new Address(1, 2, 3);
        $data = 'data';

        $sysEx = SysEx::fromBytes(
            ParameterChange::DEVICE_NUMBER,
            $address->toBinaryString().$data,
        );
        $parameterChange = ParameterChange::fromSysex($sysEx);

        self::assertEquals($address, $parameterChange->address());
        self::assertSame([\ord('d'), \ord('a'), \ord('t'), \ord('a')], $parameterChange->data());
        self::assertSame((string)$sysEx, (string)$parameterChange->toSysEx());
    }
}
