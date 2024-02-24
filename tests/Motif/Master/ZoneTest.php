<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\Master;

use Bveing\MBuddy\Motif\Channel;
use Bveing\MBuddy\Motif\Master\Zone;
use Bveing\MBuddy\Motif\Program;
use PHPUnit\Framework\TestCase;

class ZoneTest extends TestCase
{
    public function testSysExLifeCycle(): void
    {
        $srcZone = Zone::default(1);

        $sysex = $srcZone->toSysEx();
        $dstZone = Zone::fromBulkDump($sysex);

        $this->assertEquals($srcZone, $dstZone);
    }

    public function testValues(): void
    {
        $id = 3;
        $zone = Zone::default($id);
        $this->assertSame($id, $zone->id());

        $values = [
            'transmitChannel' => Channel::fromMidiByte(9),
            'isTransmittedForMidi' => true,
            'isTransmittedForToneGenerator' => false,
            'transposeOctave' => 1,
            'transposeSemitone' => 1,
            'noteLimitLow' => 1,
            'noteLimitHigh' => 126,
            'volume' => 99,
            'pan' => 65,
            'program' => new Program(1, 1, 1),
            'isBankSelectTransmittedForToneGenerator' => true,
            'isProgramChangeTransmittedForToneGenerator' => true,
            'isBankSelectTransmittedForMidi' => true,
            'isProgramChangeTransmittedForMidi' => true,
            'isRibbonTransmitted' => true,
            'isVolumeExpressionTransmitted' => true,
            'isPanTransmitted' => true,
            'isSustainTransmitted' => true,
            'isAssignableButton1Transmitted' => true,
            'isAssignableButton2Transmitted' => true,
            'isAfterTouchTransmitted' => true,
            'isPitchBendTransmitted' => true,
            'isFootController2Transmitted' => true,
            'isFootSwitchTransmitted' => true,
            'isFootController1Transmitted' => true,
            'isBreathControllerTransmitted' => true,
            'isModWheelTransmitted' => true,
            'isAssignableSliderTransmitted' => true,
            'isAssignableKnobTransmitted' => true,
            'assignableSliderControlNumber' => 1,
            'assignableKnobControlNumber' => 1,
        ];

        $other = $zone->with(...$values);
        $this->assertSame($zone->id(), $other->id());

        $this->assertSame($values['transmitChannel'], $other->transmitChannel());
        $this->assertSame($values['isTransmittedForMidi'], $other->isTransmittedForMidi());
        $this->assertSame($values['isTransmittedForToneGenerator'], $other->isTransmittedForToneGenerator());
        $this->assertSame($values['transposeOctave'], $other->transposeOctave());
        $this->assertSame($values['transposeSemitone'], $other->transposeSemitone());
        $this->assertSame($values['noteLimitLow'], $other->noteLimitLow());
        $this->assertSame($values['noteLimitHigh'], $other->noteLimitHigh());
        $this->assertSame($values['volume'], $other->volume());
        $this->assertSame($values['pan'], $other->pan());
        $this->assertSame($values['program'], $other->program());
        $this->assertSame($values['isBankSelectTransmittedForToneGenerator'], $other->isBankSelectTransmittedForToneGenerator());
        $this->assertSame($values['isProgramChangeTransmittedForToneGenerator'], $other->isProgramChangeTransmittedForToneGenerator());
        $this->assertSame($values['isBankSelectTransmittedForMidi'], $other->isBankSelectTransmittedForMidi());
        $this->assertSame($values['isProgramChangeTransmittedForMidi'], $other->isProgramChangeTransmittedForMidi());
        $this->assertSame($values['isRibbonTransmitted'], $other->isRibbonTransmitted());
        $this->assertSame($values['isVolumeExpressionTransmitted'], $other->isVolumeExpressionTransmitted());
        $this->assertSame($values['isPanTransmitted'], $other->isPanTransmitted());
        $this->assertSame($values['isSustainTransmitted'], $other->isSustainTransmitted());
        $this->assertSame($values['isAssignableButton1Transmitted'], $other->isAssignableButton1Transmitted());
        $this->assertSame($values['isAssignableButton2Transmitted'], $other->isAssignableButton2Transmitted());
        $this->assertSame($values['isAfterTouchTransmitted'], $other->isAfterTouchTransmitted());
        $this->assertSame($values['isPitchBendTransmitted'], $other->isPitchBendTransmitted());
        $this->assertSame($values['isFootController2Transmitted'], $other->isFootController2Transmitted());
        $this->assertSame($values['isFootSwitchTransmitted'], $other->isFootSwitchTransmitted());
        $this->assertSame($values['isFootController1Transmitted'], $other->isFootController1Transmitted());
        $this->assertSame($values['isBreathControllerTransmitted'], $other->isBreathControllerTransmitted());
        $this->assertSame($values['isModWheelTransmitted'], $other->isModWheelTransmitted());
        $this->assertSame($values['isAssignableSliderTransmitted'], $other->isAssignableSliderTransmitted());
        $this->assertSame($values['isAssignableKnobTransmitted'], $other->isAssignableKnobTransmitted());
        $this->assertSame($values['assignableSliderControlNumber'], $other->assignableSliderControlNumber());
        $this->assertSame($values['assignableKnobControlNumber'], $other->assignableKnobControlNumber());
    }
}
