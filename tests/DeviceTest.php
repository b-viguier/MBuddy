<?php

namespace bviguier\tests\MBuddy;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\TestUtils;
use PHPUnit\Framework\TestCase;

abstract class DeviceTest extends TestCase
{
    abstract public function createDevice(RtMidi\Input $input, RtMidi\Output $output, string $testId): MBuddy\Device;

    public function testNoInputMessage(): void
    {
        $device = $this->createDevice(new TestUtils\Input(), new TestUtils\Output(), __METHOD__);

        $counts = [
            $device->process(5),
            $device->process(5),
        ];

        $this->assertSame([0, 0], $counts);
    }

    public function testLessMessagesThanTheLimit(): void
    {
        $device = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output(), __METHOD__);
        $input->msgStack = $inputMsg = [
            RtMidi\Message::fromIntegers(0xFF),
            RtMidi\Message::fromIntegers(0xFF),
        ];

        $counts = [
            $device->process(5),
            $device->process(5),
        ];

        $this->assertSame([count($inputMsg), 0], $counts);
    }

    public function testMoreMessagesThanTheLimit(): void
    {
        $device = $this->createDevice($input = new TestUtils\Input(), new TestUtils\Output(), __METHOD__);
        $input->msgStack = $inputMsg = array_fill(0, 12, RtMidi\Message::fromIntegers(0xFF));

        $counts = [
            $device->process(5),
            $device->process(5),
            $device->process(5),
            $device->process(5),
        ];

        $this->assertSame([5, 5, 2, 0], $counts);
    }
}
