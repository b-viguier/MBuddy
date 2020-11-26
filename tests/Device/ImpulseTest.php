<?php

namespace bviguier\tests\MBuddy\Device;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\DeviceTest;
use bviguier\tests\MBuddy\TestUtils;

class ImpulseTest extends DeviceTest
{
    public function createDevice(RtMidi\Input $input, RtMidi\Output $output, string $testId): MBuddy\Device
    {
        return new MBuddy\Device\Impulse($input, $output, $this->createBank($testId));
    }

    private function createBank(string $testId): MBuddy\MidiSyxBank
    {
        $folder = new TestUtils\TempFolder($testId);
        assert($folder->directory()->getRealPath() !== false);

        return new MBuddy\MidiSyxBank($folder->directory()->getRealPath());
    }
}
