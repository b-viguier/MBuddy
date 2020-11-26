<?php

namespace bviguier\tests\MBuddy\Device;

use bviguier\MBuddy;
use bviguier\RtMidi;
use bviguier\tests\MBuddy\DeviceTest;

class Pa50Test extends DeviceTest
{
    public function createDevice(RtMidi\Input $input, RtMidi\Output $output, string $testId): MBuddy\Device
    {
        return new MBuddy\Device\Pa50($input, $output);
    }

}
