<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master;

use Bveing\MBuddy\Motif\Program;
use Bveing\MBuddy\Motif\SysEx;

class Zone
{
    private function __construct(
        private int $id,
        private int $transmitChannel,
        private bool $isTransmittedForMidi,
        private bool $isTransmittedForToneGenerator,
        private int $transposeOctave,
        private int $transposeSemitone,
        private int $noteLimitLow,
        private int $noteLimitHigh,
        private int $volume,
        private int $pan,
        private Program $program,
        private bool $isBankSelectTransmittedForToneGenerator,
        private bool $isProgramChangeTransmittedForToneGenerator,
        private bool $isBankSelectTransmittedForMidi,
        private bool $isProgramChangeTransmittedForMidi,
        private bool $isRibbonTransmitted,
        private bool $isVolumeExpressionTransmitted,
        private bool $isPanTransmitted,
        private bool $isSustainTransmitted,
        private bool $isAssignableButton1Transmitted,
        private bool $isAssignableButton2Transmitted,
        private bool $isAfterTouchTransmitted,
        private bool $isPitchBendTransmitted,
        private bool $isFootController2Transmitted,
        private bool $isFootSwitchTransmitted,
        private bool $isFootController1Transmitted,
        private bool $isBreathControllerTransmitted,
        private bool $isModWheelTransmitted,
        private bool $isAssignableSliderTransmitted,
        private bool $isAssignableKnobTransmitted,
        private int $assignableSliderControlNumber,
        private int $assignableKnobControlNumber,
    ) {
    }

    public static function default(int $id): self
    {
        return new self(
            id: $id,
            transmitChannel: 1,
            isTransmittedForMidi: false,
            isTransmittedForToneGenerator: true,
            transposeOctave: 0,
            transposeSemitone: 0,
            noteLimitLow: 0,
            noteLimitHigh: 127,
            volume: 100,
            pan: 64,
            program: new Program(0, 0, 0),
            isBankSelectTransmittedForToneGenerator: false,
            isProgramChangeTransmittedForToneGenerator: false,
            isBankSelectTransmittedForMidi: false,
            isProgramChangeTransmittedForMidi: false,
            isRibbonTransmitted: false,
            isVolumeExpressionTransmitted: false,
            isPanTransmitted: false,
            isSustainTransmitted: false,
            isAssignableButton1Transmitted: false,
            isAssignableButton2Transmitted: false,
            isAfterTouchTransmitted: false,
            isPitchBendTransmitted: false,
            isFootController2Transmitted: false,
            isFootSwitchTransmitted: false,
            isFootController1Transmitted: false,
            isBreathControllerTransmitted: false,
            isModWheelTransmitted: false,
            isAssignableSliderTransmitted: false,
            isAssignableKnobTransmitted: false,
            assignableSliderControlNumber: 0,
            assignableKnobControlNumber: 0,
        );
    }

    public static function fromBulkDump(SysEx\BulkDumpBlock $block): self
    {
        assert($block->getAddress()->h() === 0x32 && $block->getAddress()->l() === 0x00);
        $data = $block->getData();
        assert(count($data) === 0x10);

        return new self(
            id: $block->getAddress()->m(),
            transmitChannel: $data[0x00] & 0b00001111,
            isTransmittedForMidi: (bool)($data[0x00] & (1 << 4)),
            isTransmittedForToneGenerator: (bool)($data[0x00] & (1 << 5)),
            transposeOctave: $data[0x01] - 0x40,
            transposeSemitone: $data[0x02] - 0x40,
            noteLimitLow: $data[0x03],
            noteLimitHigh: $data[0x04],
            volume: $data[0x06],
            pan: $data[0x07],
            program: new Program(
                bankMsb: $data[0x08],
                bankLsb: $data[0x09],
                number: $data[0x0A],
            ),
            isBankSelectTransmittedForToneGenerator: (bool)($data[0x0B] & (1 << 0)),
            isProgramChangeTransmittedForToneGenerator: (bool)($data[0x0B] & (1 << 1)),
            isBankSelectTransmittedForMidi: (bool)($data[0x0B] & (1 << 3)),
            isProgramChangeTransmittedForMidi: (bool)($data[0x0B] & (1 << 4)),
            isRibbonTransmitted: (bool)($data[0x0B] & (1 << 2)),
            isVolumeExpressionTransmitted: (bool)($data[0x0C] & (1 << 0)),
            isPanTransmitted: (bool)($data[0x0C] & (1 << 1)),
            isSustainTransmitted: (bool)($data[0x0C] & (1 << 2)),
            isAssignableButton1Transmitted: (bool)($data[0x0C] & (1 << 3)),
            isAssignableButton2Transmitted: (bool)($data[0x0C] & (1 << 4)),
            isAfterTouchTransmitted: (bool)($data[0x0C] & (1 << 5)),
            isPitchBendTransmitted: (bool)($data[0x0C] & (1 << 6)),
            isFootController2Transmitted: (bool)($data[0x0D] & (1 << 0)),
            isFootSwitchTransmitted: (bool)($data[0x0D] & (1 << 1)),
            isFootController1Transmitted: (bool)($data[0x0D] & (1 << 2)),
            isBreathControllerTransmitted: (bool)($data[0x0D] & (1 << 3)),
            isModWheelTransmitted: (bool)($data[0x0D] & (1 << 4)),
            isAssignableSliderTransmitted: (bool)($data[0x0D] & (1 << 5)),
            isAssignableKnobTransmitted: (bool)($data[0x0D] & (1 << 6)),
            assignableSliderControlNumber: $data[0x0E],
            assignableKnobControlNumber: $data[0x0F],
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTransmitChannel(): int
    {
        return $this->transmitChannel;
    }

    public function with(
        int $id = null,
        int $transmitChannel = null,
        bool $isTransmittedForMidi = null,
        bool $isTransmittedForToneGenerator = null,
        int $transposeOctave = null,
        int $transposeSemitone = null,
        int $noteLimitLow = null,
        int $noteLimitHigh = null,
        int $volume = null,
        int $pan = null,
        Program $program = null,
        bool $isBankSelectTransmittedForToneGenerator = null,
        bool $isProgramChangeTransmittedForToneGenerator = null,
        bool $isBankSelectTransmittedForMidi = null,
        bool $isProgramChangeTransmittedForMidi = null,
        bool $isRibbonTransmitted = null,
        bool $isVolumeExpressionTransmitted = null,
        bool $isPanTransmitted = null,
        bool $isSustainTransmitted = null,
        bool $isAssignableButton1Transmitted = null,
        bool $isAssignableButton2Transmitted = null,
        bool $isAfterTouchTransmitted = null,
        bool $isPitchBendTransmitted = null,
        bool $isFootController2Transmitted = null,
        bool $isFootSwitchTransmitted = null,
        bool $isFootController1Transmitted = null,
        bool $isBreathControllerTransmitted = null,
        bool $isModWheelTransmitted = null,
        bool $isAssignableSliderTransmitted = null,
        bool $isAssignableKnobTransmitted = null,
        int $assignableSliderControlNumber = null,
        int $assignableKnobControlNumber = null,
    ): self {
        return new self(
            $id ?? $this->id,
            $transmitChannel ?? $this->transmitChannel,
            $isTransmittedForMidi ?? $this->isTransmittedForMidi,
            $isTransmittedForToneGenerator ?? $this->isTransmittedForToneGenerator,
            $transposeOctave ?? $this->transposeOctave,
            $transposeSemitone ?? $this->transposeSemitone,
            $noteLimitLow ?? $this->noteLimitLow,
            $noteLimitHigh ?? $this->noteLimitHigh,
            $volume ?? $this->volume,
            $pan ?? $this->pan,
            $program ?? $this->program,
            $isBankSelectTransmittedForToneGenerator ?? $this->isBankSelectTransmittedForToneGenerator,
            $isProgramChangeTransmittedForToneGenerator ?? $this->isProgramChangeTransmittedForToneGenerator,
            $isBankSelectTransmittedForMidi ?? $this->isBankSelectTransmittedForMidi,
            $isProgramChangeTransmittedForMidi ?? $this->isProgramChangeTransmittedForMidi,
            $isRibbonTransmitted ?? $this->isRibbonTransmitted,
            $isVolumeExpressionTransmitted ?? $this->isVolumeExpressionTransmitted,
            $isPanTransmitted ?? $this->isPanTransmitted,
            $isSustainTransmitted ?? $this->isSustainTransmitted,
            $isAssignableButton1Transmitted ?? $this->isAssignableButton1Transmitted,
            $isAssignableButton2Transmitted ?? $this->isAssignableButton2Transmitted,
            $isAfterTouchTransmitted ?? $this->isAfterTouchTransmitted,
            $isPitchBendTransmitted ?? $this->isPitchBendTransmitted,
            $isFootController2Transmitted ?? $this->isFootController2Transmitted,
            $isFootSwitchTransmitted ?? $this->isFootSwitchTransmitted,
            $isFootController1Transmitted ?? $this->isFootController1Transmitted,
            $isBreathControllerTransmitted ?? $this->isBreathControllerTransmitted,
            $isModWheelTransmitted ?? $this->isModWheelTransmitted,
            $isAssignableSliderTransmitted ?? $this->isAssignableSliderTransmitted,
            $isAssignableKnobTransmitted ?? $this->isAssignableKnobTransmitted,
            $assignableSliderControlNumber ?? $this->assignableSliderControlNumber,
            $assignableKnobControlNumber ?? $this->assignableKnobControlNumber,
        );
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
                0x00 => (0b00001111 & $this->transmitChannel) | ($this->isTransmittedForMidi << 4) | ($this->isTransmittedForToneGenerator << 5),
                0x01 => 0x40 + $this->transposeOctave,
                0x02 => 0x40 + $this->transposeSemitone,
                0x03 => $this->noteLimitLow,
                0x04 => $this->noteLimitHigh,
                0x05 => 0x00, // Reserved
                0x06 => $this->volume,
                0x07 => $this->pan,
                0x08 => $this->program->getBankMsb(),
                0x09 => $this->program->getBankLsb(),
                0x0A => $this->program->getNumber(),
                0x0B => $this->isBankSelectTransmittedForToneGenerator << 0
                    | $this->isProgramChangeTransmittedForToneGenerator << 1
                    | $this->isRibbonTransmitted << 2
                    | $this->isBankSelectTransmittedForMidi << 3
                    | $this->isProgramChangeTransmittedForMidi << 4,
                0x0C => $this->isVolumeExpressionTransmitted << 0
                    | $this->isPanTransmitted << 1
                    | $this->isSustainTransmitted << 2
                    | $this->isAssignableButton1Transmitted << 3
                    | $this->isAssignableButton2Transmitted << 4
                    | $this->isAfterTouchTransmitted << 5
                    | $this->isPitchBendTransmitted << 6,
                0x0D => $this->isFootController2Transmitted << 0
                    | $this->isFootSwitchTransmitted << 1
                    | $this->isFootController1Transmitted << 2
                    | $this->isBreathControllerTransmitted << 3
                    | $this->isModWheelTransmitted << 4
                    | $this->isAssignableSliderTransmitted << 5
                    | $this->isAssignableKnobTransmitted << 6,
                0x0E => $this->assignableSliderControlNumber,
                0x0F => $this->assignableKnobControlNumber,
            ],
        );
    }
}
