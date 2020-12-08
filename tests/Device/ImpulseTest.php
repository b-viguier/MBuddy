<?php

namespace bviguier\tests\MBuddy\Device;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\DeviceTest;
use bviguier\tests\MBuddy\TestUtils;
use Monolog\Logger;

class ImpulseTest extends DeviceTest
{
    public function createDevice(RtMidi\Input $input, RtMidi\Output $output, string $testId): MBuddy\Device\Impulse
    {
        return new MBuddy\Device\Impulse(
            $input,
            $output,
            $this->createBank($testId),
            new Logger('null'),
        );
    }

    private function createBank(string $testId): MBuddy\MidiSyxBank
    {
        $folder = new TestUtils\TempFolder($testId);
        assert($folder->directory()->getRealPath() !== false);

        return new MBuddy\MidiSyxBank($folder->directory()->getRealPath());
    }

    public function testDoLoadPresetSignature(): void
    {
        $impulse = $this->createDevice(new TestUtils\Input(), new TestUtils\Output(), __METHOD__);

        TestUtils\FunctionSignature::assertSameSignature(
            function (MBuddy\Preset $p) {
            }, $impulse->doLoadPreset()
        );
    }

    public function testDoLoadPresetSendProgramChange(): void
    {
        $impulse = new MBuddy\Device\Impulse(
            new TestUtils\Input(),
            $output = new TestUtils\Output(),
            $bank = $this->createBank(__METHOD__),
            new Logger('null'),
        );
        $progId = $bank->save('name', 'data');
        assert($progId !== null);

        // Can load existing preset
        $impulse->doLoadPreset()(new MBuddy\Preset(0, 0, $progId));
        $msg = array_pop($output->msgStack);
        assert($msg !== null);
        $this->assertEmpty($output->msgStack);
        $this->assertSame('data', $msg->toBinString());

        // Cannot load other program
        $impulse->doLoadPreset()(new MBuddy\Preset(0, 0, $progId + 1));
        $this->assertEmpty($output->msgStack);

        // Bank must be 0,0
        $impulse->doLoadPreset()(new MBuddy\Preset(1, 1, $progId));
        $this->assertEmpty($output->msgStack);
    }

    public function testMessagesAreForwarded(): void
    {
        $impulse = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output(), __METHOD__);
        /** @var array<RtMidi\Message> $msgStack */
        $msgStack = [];
        // Arbitrary messages are forwarded
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xFF);
        $impulse->onMidiEvent(function (RtMidi\Message $msg) use (&$msgStack): void {
            $msgStack[] = $msg;
        });
        $count = $impulse->process(2);

        $this->assertSame(1, $count);
        $this->assertEmpty($input->msgStack);
        $this->assertCount(1, $msgStack);
        $this->assertSame([0xFF], $msgStack[0]->toIntegers());
    }

    public function testSysexAreStoredInBank(): void
    {
        $impulse = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output(), __METHOD__);
        /** @var array<MBuddy\Preset> $presetStack */
        $presetStack = [];
        /** @var array<RtMidi\Message> $msgStack */
        $msgStack = [];
        $syxData = chr(0xF0) . 'data';
        // Arbitrary messages are forwarded
        $input->msgStack[] = RtMidi\Message::fromBinString($syxData);
        $impulse->onPresetSaved(function (MBuddy\Preset $preset) use (&$presetStack): void {
            $presetStack[] = $preset;
        });
        $impulse->onMidiEvent(function (RtMidi\Message $msg) use (&$msgStack): void {
            $msgStack[] = $msg;
        });
        $count = $impulse->process(2);

        $this->assertSame(1, $count);
        $this->assertEmpty($input->msgStack);
        $this->assertEmpty($msgStack);
        $this->assertCount(1, $presetStack);
        $this->assertSame(0, $presetStack[0]->bankMSB());
        $this->assertSame(0, $presetStack[0]->bankLSB());
        $this->assertSame(0, $presetStack[0]->program());
    }
}
