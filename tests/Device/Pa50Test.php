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

    public function testDoModifySongIdOfCurrentPerformanceSignature(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), new TestUtils\Output());
        TestUtils\FunctionSignature::assertSameSignature(
            function(MBuddy\SongId $songId): void {},
            $pa50->doModifySongIdOfCurrentPerformance(),
        );
    }

    public function testdoModifySongIdOfCurrentPerformanceSendsMessages(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), $output = new TestUtils\Output());
        $songId = new MBuddy\SongId($id = 5);

        $pa50->doModifySongIdOfCurrentPerformance()($songId);

        $this->assertCount(3, $output->msgStack);
        $this->assertEquals($output->msgStack[0], RtMidi\Message::fromIntegers(0xBF, 0x00, MBuddy\Device\Pa50::MBUDDY_BANK_MSB));
        $this->assertEquals($output->msgStack[1], RtMidi\Message::fromIntegers(0xBF, 0x20, MBuddy\Device\Pa50::MBUDDY_BANK_LSB));
        $this->assertEquals($output->msgStack[2], RtMidi\Message::fromIntegers(0xCF, $id));
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

    public function testSongIdChangesAreNotified(): void
    {
        $pa50 = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output());
        // This message should be processed
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x00, MBuddy\Device\Pa50::MBUDDY_BANK_MSB);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x20, MBuddy\Device\Pa50::MBUDDY_BANK_LSB);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xCF, $expectedId = 3);
        // Not this one (wrong MSB)
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x00, MBuddy\Device\Pa50::MBUDDY_BANK_MSB+1);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x20, MBuddy\Device\Pa50::MBUDDY_BANK_LSB);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xCF, $expectedId + 1);
        // Not this one (wrong LSB)
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x00, MBuddy\Device\Pa50::MBUDDY_BANK_MSB);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x20, MBuddy\Device\Pa50::MBUDDY_BANK_LSB+1);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xCF, $expectedId + 1);
        // Not this one (missing LSB)
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xBF, 0x00, MBuddy\Device\Pa50::MBUDDY_BANK_MSB);
        $input->msgStack[] = RtMidi\Message::fromIntegers(0xCF, $expectedId + 1);

        /** @var array<MBuddy\SongId> $songsStack */
        $songsStack = [];

        $pa50->onSongChanged(function(MBuddy\SongId $songId) use(&$songsStack): void {
            $songsStack[] = $songId;
        });
        $count = $pa50->process(20);

        $this->assertSame(11, $count);
        $this->assertCount(1, $songsStack);
        $this->assertEquals($expectedId, $songsStack[0]->id());
    }

    public function testSongsIdModification(): void
    {
        $pa50 = $this->createDevice(new TestUtils\Input(), $output = new TestUtils\Output());
        $songId = new MBuddy\SongId($id = 5);

        $pa50->doModifySongIdOfCurrentPerformance()($songId);

        $this->assertCount(3, $output->msgStack);
        $this->assertEquals($output->msgStack[0], RtMidi\Message::fromIntegers(0xBF, 0x00, MBuddy\Device\Pa50::MBUDDY_BANK_MSB));
        $this->assertEquals($output->msgStack[1], RtMidi\Message::fromIntegers(0xBF, 0x20, MBuddy\Device\Pa50::MBUDDY_BANK_LSB));
        $this->assertEquals($output->msgStack[2], RtMidi\Message::fromIntegers(0xCF, $id));
    }
}
