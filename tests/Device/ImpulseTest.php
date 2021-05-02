<?php

namespace bviguier\tests\MBuddy\Device;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\Device\Impulse\PatchTest;
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
            function (MBuddy\SongId $p) {
            }, $impulse->doLoadSong()
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
        $this->assertTrue($bank->save($id = 1, 'name', PatchTest::validSysex()));

        // Can load existing SongId
        $impulse->doLoadSong()(new MBuddy\SongId($id));

        $msg = array_pop($output->msgStack);
        assert($msg !== null);
        $this->assertEmpty($output->msgStack);
        $this->assertSame(PatchTest::validSysex(), $msg->toBinString());

        // Cannot load other SongId
        $impulse->doLoadSong()(new MBuddy\SongId($id + 1));
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

    public function testSongModification(): void
    {
        $impulse = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output(), __METHOD__);
        /** @var array<MBuddy\SongId> $songsStack */
        $songsStack = [];
        /** @var array<RtMidi\Message> $msgStack */
        $msgStack = [];

        // Record button pressed
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xB4, 0x75, 0x7F);
        // Previous button pressed
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xB4, 0x70, 0x7F);
        // Next button pressed
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xB4, 0x71, 0x7F);
        // Record button release
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xB4, 0x75, 0x00);

        // Next button pressed
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xB4, 0x71, 0x7F);
        // Previous button pressed
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xB4, 0x70, 0x7F);

        $impulse->onSongIdModified(function (MBuddy\SongId $songId) use (&$songsStack): void {
            $songsStack[] = $songId;
        });
        $impulse->onMidiEvent(function (RtMidi\Message $msg) use (&$msgStack): void {
            $msgStack[] = $msg;
        });
        $count = $impulse->process(count($input->msgStack) + 1);

        $this->assertSame(6, $count);
        $this->assertEmpty($input->msgStack);
        $this->assertEmpty($msgStack);
        $this->assertCount(2, $songsStack);
        $this->assertSame(1, $songsStack[0]->id());
        $this->assertSame(2, $songsStack[1]->id());
    }
}
