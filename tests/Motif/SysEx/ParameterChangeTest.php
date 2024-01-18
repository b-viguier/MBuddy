<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Motif\SysEx\ParameterChange;
use Bveing\MBuddy\Motif\SysEx\Address;
use Bveing\MBuddy\Motif\SysEx;

class ParameterChangeTest extends TestCase
{
    public function testValues(): void
    {
        $address = new Address(1, 2, 3);
        $data = [1, 2, 3, 4];

        $parameterChange = ParameterChange::create($address, $data);
        $sysex = $parameterChange->toSysex();

        self::assertSame($address, $parameterChange->getAddress());
        self::assertSame($data, $parameterChange->getData());
        self::assertSame(ParameterChange::DEVICE_NUMBER, $sysex->getDeviceNumber());
        self::assertSame([...$address->toArray(), ...$data], $sysex->getBytes());
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
            SysEx::fromData(ParameterChange::DEVICE_NUMBER + 1, '*'),
            'Invalid Device Number',
        ];
        yield 'invalid size' => [
            SysEx::fromData(ParameterChange::DEVICE_NUMBER, '*'),
            'Invalid BulkDump size',
        ];
    }

    public function testFromSysEx(): void
    {
        $address = new Address(1, 2, 3);
        $data = 'data';

        $sysEx = SysEx::fromData(
            ParameterChange::DEVICE_NUMBER,
            $address->toBinaryString().$data,
        );
        $parameterChange = ParameterChange::fromSysex($sysEx);

        self::assertEquals($address, $parameterChange->getAddress());
        self::assertSame([ord('d'), ord('a'), ord('t'), ord('a')], $parameterChange->getData());
        self::assertSame((string)$sysEx, (string)$parameterChange->toSysEx());
    }
}
