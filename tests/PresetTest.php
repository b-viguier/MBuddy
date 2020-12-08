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

    /**
     * @dataProvider stringConversionProvider
     */
    public function testStringConversion(MBuddy\Preset $preset, string $expected): void
    {
        $this->assertSame($expected, (string) $preset);
    }

    /**
     * @return iterable<array{0:MBuddy\Preset,1:string}>
     */
    public function stringConversionProvider(): iterable
    {
        yield [new MBuddy\Preset(0x00, 0x04, 0x1A), '00|04|1A'];
        yield [new MBuddy\Preset(0x0F, 0xAB, 0x00), '0F|AB|00'];
        yield [new MBuddy\Preset(0xFF, 0x00, 0x09), 'FF|00|09'];
    }
}
