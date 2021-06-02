<?php

namespace bviguier\tests\MBuddy\Device;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\TestUtils;
use bviguier\tests\MBuddy\DeviceTest;
use Monolog\Logger;

class IPadTest extends DeviceTest
{
    public function createDevice(RtMidi\Input $input, RtMidi\Output $output, string $testId = ''): MBuddy\Device\IPad
    {
        return new MBuddy\Device\IPad(
            $input,
            $output,
            new Logger('null'),
        );
    }

    public function testDoLoadPresetSignature(): void
    {
        $ipad = $this->createDevice(new TestUtils\Input(), new TestUtils\Output());

        TestUtils\FunctionSignature::assertSameSignature(
            function (MBuddy\SongId $p) {
            }, $ipad->doLoadSong()
        );
    }

    public function testDoLoadPresetSendProgramChange(): void
    {
        $ipad = $this->createDevice(new TestUtils\Input(), $output = new TestUtils\Output());

        // ProgramId are forwarded
        $ipad->doLoadSong()(new MBuddy\SongId($id = 1));

        $msg = array_pop($output->msgStack);
        assert($msg !== null);
        $this->assertEmpty($output->msgStack);
        $this->assertSame([0xCF, $id], $msg->toIntegers());
    }
}
