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

        $this->assertEquals(Master\Id::editBuffer(), $master->id());
        $this->assertSame(0, $master->zone0()->id());
        $this->assertSame(1, $master->zone1()->id());
        $this->assertSame(2, $master->zone2()->id());
        $this->assertSame(3, $master->zone3()->id());
        $this->assertSame(4, $master->zone4()->id());
        $this->assertSame(5, $master->zone5()->id());
        $this->assertSame(6, $master->zone6()->id());
        $this->assertSame(7, $master->zone7()->id());
    }

    public function testSysExLifeCycle(): void
    {
        $master = Master::default();

        $blocks = [];
        foreach ($master->toBulkDumpBlocks() as $block) {
            $blocks[] = $block;
        }
        $this->assertCount(Master::DUMP_NB_BLOCKS, $blocks);

        $dstMaster = Master::fromBulkDumpBlocks(...$blocks);

        $this->assertEquals($master, $dstMaster);
    }
}
