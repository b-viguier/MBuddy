<?php

namespace bviguier\tests\MBuddy\Device;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\DeviceTest;
use bviguier\tests\MBuddy\TestUtils;
use Monolog\Logger;

class Pa50Test extends DeviceTest
{
    public function createDevice(RtMidi\Input $input, RtMidi\Output $output, string $testId = ''): MBuddy\Device\Pa50
    {
        return new MBuddy\Device\Pa50(
            $input,
            $output,
            new Logger('null'),
        );
    }

    public function testDoSaveExternalPresetSignature(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), new TestUtils\Output());
        TestUtils\FunctionSignature::assertSameSignature(
            function(MBuddy\Preset $preset): void {},
            $pa50->doSaveExternalPreset(),
        );
    }

    public function testDoSaveExternalPresetSendsMessages(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), $output = new TestUtils\Output());
        $preset = new MBuddy\Preset($msb = 3,$lsb = 4,$prg = 5);

        $pa50->doSaveExternalPreset()($preset);

        $this->assertCount(3, $output->msgStack);
        $this->assertEquals($output->msgStack[0], RtMidi\Message::fromIntegers(0xBF, 0x00, $msb));
        $this->assertEquals($output->msgStack[1], RtMidi\Message::fromIntegers(0xBF, 0x20, $lsb));
        $this->assertEquals($output->msgStack[2], RtMidi\Message::fromIntegers(0xCF, $prg));
    }

    public function testDoPlayEventSignature(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), new TestUtils\Output());
        TestUtils\FunctionSignature::assertSameSignature(
            function(RtMidi\Message $msg): void {},
            $pa50->doPlayEvent(),
        );
    }

    public function testDoPlayEventSendsMessage(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), $output = new TestUtils\Output());
        $msg = RtMidi\Message::fromBinString('data');

        $pa50->doPlayEvent()($msg);

        $this->assertCount(1, $output->msgStack);
        $this->assertSame($output->msgStack[0], $msg);
    }

    public function testPresetChangesAreNotified(): void
    {
        $pa50 = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output());
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x00, $msb = 1);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x20, $lsb = 2);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xCF, $prg = 3);
        /** @var array<MBuddy\Preset> $presetStack */
        $presetStack = [];

        $pa50->onExternalPresetLoaded(function(MBuddy\Preset $preset) use(&$presetStack): void {
            $presetStack[] = $preset;
        });
        $count = $pa50->process(4);

        $this->assertSame(3, $count);
        $this->assertCount(1, $presetStack);
        $this->assertEquals(new MBuddy\Preset($msb, $lsb, $prg), $presetStack[0]);
    }
}
