<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class MasterZone
{
    private Program $program;

    public function __construct(
        private int $id,
        private int $transmitChannel = 1,
        private bool $isTransmittedForToneGenerator = true,
        private bool $isTransmittedForMidi = false,
        private int $volume = 100,
        private int $pan = 64,
        ?Program $program = null,
        private int $transposeOctave = 0,
        private int $transposeSemitone = 0,
        private int $noteLimitLow = 0,
        private int $noteLimitHigh = 127,
        private int $assignableSliderControlNumber = 0,
        private int $assignableKnobControlNumber = 0,
        private bool $isBankSelectTransmittedForToneGenerator = false,
        private bool $isBankSelectTransmittedForMidi = false,
        private bool $isProgramChangeTransmittedForToneGenerator = false,
        private bool $isProgramChangeTransmittedForMidi = false,
        private bool $isRibbonTransmitted = false,
        private bool $isVolumeExpressionTransmitted = false,
        private bool $isPanTransmitted = false,
        private bool $isSustainTransmitted = false,
        private bool $isAssignableButton1Transmitted = false,
        private bool $isAssignableButton2Transmitted = false,
        private bool $isFootController1Transmitted = false,
        private bool $isFootController2Transmitted = false,
        private bool $isFootSwitchTransmitted = false,
        private bool $isAfterTouchTransmitted = false,
        private bool $isModWheelTransmitted = false,
        private bool $isPitchBendTransmitted = false,
        private bool $isBreathControllerTransmitted = false,
        private bool $isAssignableSliderTransmitted = false,
        private bool $isAssignableKnobTransmitted = false,
    ) {
        $this->program = $program ?? new Program(0, 0, 0);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTransmitChannel(): int
    {
        return $this->transmitChannel;
    }

    public function isTransmittedForToneGenerator(): bool
    {
        return $this->isTransmittedForToneGenerator;
    }

    public function isTransmittedForMidi(): bool
    {
        return $this->isTransmittedForMidi;
    }

    public function getTransposeOctave(): int
    {
        return $this->transposeOctave;
    }

    public function getTransposeSemitone(): int
    {
        return $this->transposeSemitone;
    }

    public function getNoteLimitLow(): int
    {
        return $this->noteLimitLow;
    }

    public function getNoteLimitHigh(): int
    {
        return $this->noteLimitHigh;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function getPan(): int
    {
        return $this->pan;
    }

    public function getProgram(): Program
    {
        return $this->program;
    }

    public function getPreset(): Preset
    {
        return new Preset();
    }

    public function isBankSelectTransmittedForToneGenerator(): bool
    {
        return $this->isBankSelectTransmittedForToneGenerator;
    }

    public function isBankSelectTransmittedForMidi(): bool
    {
        return $this->isBankSelectTransmittedForMidi;
    }

    public function isProgramChangeTransmittedForToneGenerator(): bool
    {
        return $this->isProgramChangeTransmittedForToneGenerator;
    }

    public function isProgramChangeTransmittedForMidi(): bool
    {
        return $this->isProgramChangeTransmittedForMidi;
    }

    public function isRibbonTransmitted(): bool
    {
        return $this->isRibbonTransmitted;
    }

    public function isVolumeExpressionTransmitted(): bool
    {
        return $this->isVolumeExpressionTransmitted;
    }

    public function isPanTransmitted(): bool
    {
        return $this->isPanTransmitted;
    }

    public function isSustainTransmitted(): bool
    {
        return $this->isSustainTransmitted;
    }

    public function isAssignableButton1Transmitted(): bool
    {
        return $this->isAssignableButton1Transmitted;
    }

    public function isAssignableButton2Transmitted(): bool
    {
        return $this->isAssignableButton2Transmitted;
    }

    public function isFootController1Transmitted(): bool
    {
        return $this->isFootController1Transmitted;
    }

    public function isFootController2Transmitted(): bool
    {
        return $this->isFootController2Transmitted;
    }

    public function isFootSwitchTransmitted(): bool
    {
        return $this->isFootSwitchTransmitted;
    }

    public function isAfterTouchTransmitted(): bool
    {
        return $this->isAfterTouchTransmitted;
    }

    public function isModWheelTransmitted(): bool
    {
        return $this->isModWheelTransmitted;
    }

    public function isPitchBendTransmitted(): bool
    {
        return $this->isPitchBendTransmitted;
    }

    public function isBreathControllerTransmitted(): bool
    {
        return $this->isBreathControllerTransmitted;
    }

    public function isAssignableSliderTransmitted(): bool
    {
        return $this->isAssignableSliderTransmitted;
    }

    public function isAssignableKnobTransmitted(): bool
    {
        return $this->isAssignableKnobTransmitted;
    }

    public function getAssignableSliderControlNumber(): int
    {
        return $this->assignableSliderControlNumber;
    }

    public function getAssignableKnobControlNumber(): int
    {
        return $this->assignableKnobControlNumber;
    }

    public function getSysEx(): SysEx\BulkDumpBlock
    {
        return SysEx\BulkDumpBlock::create(
            byteCount: 0x10,
            address: new SysEx\Address(0x32, $this->id, 0x00),
            data: [
                (0b00001111 & $this->transmitChannel) | ($this->isTransmittedForMidi << 4) | ($this->isTransmittedForToneGenerator << 5),
                0x40 + $this->transposeOctave,
                0x40 + $this->transposeSemitone,
                $this->noteLimitLow,
                $this->noteLimitHigh,
                0x00, // Reserved
                $this->volume,
                $this->pan,
                $this->program->getBankMsb(),
                $this->program->getBankLsb(),
                $this->program->getNumber(),
                $this->isBankSelectTransmittedForToneGenerator << 0
                | $this->isProgramChangeTransmittedForToneGenerator << 1
                | $this->isRibbonTransmitted << 2
                | $this->isBankSelectTransmittedForMidi << 3
                | $this->isProgramChangeTransmittedForMidi << 4,
                $this->isVolumeExpressionTransmitted << 0
                | $this->isPanTransmitted << 1
                | $this->isSustainTransmitted << 2
                | $this->isAssignableButton1Transmitted << 3
                | $this->isAssignableButton2Transmitted << 4
                | $this->isAfterTouchTransmitted << 5
                | $this->isPitchBendTransmitted << 6,
                $this->isFootController2Transmitted << 0
                | $this->isFootSwitchTransmitted << 1
                | $this->isFootController1Transmitted << 2
                | $this->isBreathControllerTransmitted << 3
                | $this->isModWheelTransmitted << 4
                | $this->isAssignableSliderTransmitted << 5
                | $this->isAssignableKnobTransmitted << 6,
                $this->assignableSliderControlNumber,
                $this->assignableKnobControlNumber,
            ]
        );
    }
}
