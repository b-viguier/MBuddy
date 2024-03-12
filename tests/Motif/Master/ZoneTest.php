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

        self::assertEquals($srcZone, $dstZone);
    }

    public function testValues(): void
    {
        $id = 3;
        $zone = Zone::default($id);
        self::assertSame($id, $zone->id());

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
        self::assertSame($zone->id(), $other->id());

        self::assertSame($values['transmitChannel'], $other->transmitChannel());
        self::assertSame($values['isTransmittedForMidi'], $other->isTransmittedForMidi());
        self::assertSame($values['isTransmittedForToneGenerator'], $other->isTransmittedForToneGenerator());
        self::assertSame($values['transposeOctave'], $other->transposeOctave());
        self::assertSame($values['transposeSemitone'], $other->transposeSemitone());
        self::assertSame($values['noteLimitLow'], $other->noteLimitLow());
        self::assertSame($values['noteLimitHigh'], $other->noteLimitHigh());
        self::assertSame($values['volume'], $other->volume());
        self::assertSame($values['pan'], $other->pan());
        self::assertSame($values['program'], $other->program());
        self::assertSame($values['isBankSelectTransmittedForToneGenerator'], $other->isBankSelectTransmittedForToneGenerator());
        self::assertSame($values['isProgramChangeTransmittedForToneGenerator'], $other->isProgramChangeTransmittedForToneGenerator());
        self::assertSame($values['isBankSelectTransmittedForMidi'], $other->isBankSelectTransmittedForMidi());
        self::assertSame($values['isProgramChangeTransmittedForMidi'], $other->isProgramChangeTransmittedForMidi());
        self::assertSame($values['isRibbonTransmitted'], $other->isRibbonTransmitted());
        self::assertSame($values['isVolumeExpressionTransmitted'], $other->isVolumeExpressionTransmitted());
        self::assertSame($values['isPanTransmitted'], $other->isPanTransmitted());
        self::assertSame($values['isSustainTransmitted'], $other->isSustainTransmitted());
        self::assertSame($values['isAssignableButton1Transmitted'], $other->isAssignableButton1Transmitted());
        self::assertSame($values['isAssignableButton2Transmitted'], $other->isAssignableButton2Transmitted());
        self::assertSame($values['isAfterTouchTransmitted'], $other->isAfterTouchTransmitted());
        self::assertSame($values['isPitchBendTransmitted'], $other->isPitchBendTransmitted());
        self::assertSame($values['isFootController2Transmitted'], $other->isFootController2Transmitted());
        self::assertSame($values['isFootSwitchTransmitted'], $other->isFootSwitchTransmitted());
        self::assertSame($values['isFootController1Transmitted'], $other->isFootController1Transmitted());
        self::assertSame($values['isBreathControllerTransmitted'], $other->isBreathControllerTransmitted());
        self::assertSame($values['isModWheelTransmitted'], $other->isModWheelTransmitted());
        self::assertSame($values['isAssignableSliderTransmitted'], $other->isAssignableSliderTransmitted());
        self::assertSame($values['isAssignableKnobTransmitted'], $other->isAssignableKnobTransmitted());
        self::assertSame($values['assignableSliderControlNumber'], $other->assignableSliderControlNumber());
        self::assertSame($values['assignableKnobControlNumber'], $other->assignableKnobControlNumber());
    }
}
