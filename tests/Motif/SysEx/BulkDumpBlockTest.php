<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx;

use Bveing\MBuddy\Motif\SysEx;
use Bveing\MBuddy\Motif\SysEx\Address;
use Bveing\MBuddy\Motif\SysEx\BulkDumpBlock;
use PHPUnit\Framework\TestCase;

class BulkDumpBlockTest extends TestCase
{
    public function testLifeCycle(): void
    {
        $byteCountMsb = 2;
        $byteCountLsb = 3;
        $byteCount = $byteCountMsb * 128 + $byteCountLsb;
        $checksum = 66;

        $address = new Address(0x01, 0x02, 0x03);
        $data = \array_map(fn($byte) => $byte % 0xF0, \range(0, $byteCount - 1));
        $block = BulkDumpBlock::create($byteCount, $address, $data);
        $sysex = $block->toSysEx();

        self::assertSame($address, $block->address());
        self::assertSame($data, $block->data());
        self::assertSame(BulkDumpBlock::DEVICE_NUMBER, $sysex->deviceNumber());
        self::assertSame(
            [$byteCountMsb, $byteCountLsb, ...$address->toArray(), ...$data, $checksum],
            $sysex->toBytes(),
        );

        self::assertFalse($block->isHeaderBlock());
        self::assertFalse($block->isFooterBlock());

        $blockFromSysex = BulkDumpBlock::fromSysEx($sysex);
        self::assertEquals($block, $blockFromSysex);
    }

    /**
     * @dataProvider invalidSysExProvider
     */
    public function testFromInvalidSysEx(SysEx $sysEx, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        BulkDumpBlock::fromSysEx($sysEx);
    }

    /**
     * @return iterable<string, array{0: SysEx, 1: string}>
     */
    public static function invalidSysExProvider(): iterable
    {
        yield 'invalid device number' => [
            SysEx::fromBytes(BulkDumpBlock::DEVICE_NUMBER + 1, '*'),
            'Invalid Device Number',
        ];
        yield 'invalid size' => [
            SysEx::fromBytes(BulkDumpBlock::DEVICE_NUMBER, '*'),
            'Invalid BulkDump size',
        ];

        $size = 2;
        $address = [7, 8, 9];
        $data = [1, 2];
        $checksum = 99;

        yield 'wrong size' => [
            SysEx::fromBytes(
                BulkDumpBlock::DEVICE_NUMBER,
                \pack('C*', 0, $size),
            ),
            'Invalid BulkDump size',
        ];

        yield 'wrong checksum' => [
            SysEx::fromBytes(
                BulkDumpBlock::DEVICE_NUMBER,
                \pack('C*', ...[0, $size, ...$address, ...$data, $checksum + 1]),
            ),
            'Invalid Checksum',
        ];
    }

    public function testHeaderBlock(): void
    {
        $block = BulkDumpBlock::createHeaderBlock($m = 0x01, $l = 0x02);

        self::assertTrue($block->isHeaderBlock());
        self::assertFalse($block->isFooterBlock());
        self::assertEquals(new Address(0x0E, $m, $l), $block->address());
        self::assertEmpty($block->data());

        $blockFromSysex = BulkDumpBlock::fromSysEx($block->toSysEx());

        self::assertTrue($blockFromSysex->isHeaderBlock());
    }

    public function testFooterBlock(): void
    {
        $block = BulkDumpBlock::createFooterBlock($m = 0x01, $l = 0x02);

        self::assertTrue($block->isFooterBlock());
        self::assertFalse($block->isHeaderBlock());
        self::assertEquals(new Address(0x0F, $m, $l), $block->address());
        self::assertEmpty($block->data());

        $blockFromSysex = BulkDumpBlock::fromSysEx($block->toSysEx());

        self::assertTrue($blockFromSysex->isFooterBlock());
    }
}
