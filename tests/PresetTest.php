<?php

namespace bviguier\tests\MBuddy;

use bviguier\MBuddy;
use PHPUnit\Framework\TestCase;

class PresetTest extends TestCase
{
    public function testGetValues(): void
    {
        $preset = new MBuddy\Preset($msb = 1, $lsb = 2, $program = 3);

        $this->assertSame($msb, $preset->bankMSB());
        $this->assertSame($lsb, $preset->bankLSB());
        $this->assertSame($program, $preset->program());
    }
}
