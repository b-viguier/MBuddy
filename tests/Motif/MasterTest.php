<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif;

use Bveing\MBuddy\Motif\Master;
use PHPUnit\Framework\TestCase;

class MasterTest extends TestCase
{
    public function testDefault(): void
    {
        $master = Master::default();

        self::assertEquals(Master\Id::editBuffer(), $master->id());
        self::assertSame(0, $master->zone0()->id());
        self::assertSame(1, $master->zone1()->id());
        self::assertSame(2, $master->zone2()->id());
        self::assertSame(3, $master->zone3()->id());
        self::assertSame(4, $master->zone4()->id());
        self::assertSame(5, $master->zone5()->id());
        self::assertSame(6, $master->zone6()->id());
        self::assertSame(7, $master->zone7()->id());
    }

    public function testSysExLifeCycle(): void
    {
        $master = Master::default();

        $blocks = [];
        foreach ($master->toBulkDumpBlocks() as $block) {
            $blocks[] = $block;
        }
        self::assertCount(Master::DUMP_NB_BLOCKS, $blocks);

        $dstMaster = Master::fromBulkDumpBlocks(...$blocks);

        self::assertEquals($master, $dstMaster);
    }
}
