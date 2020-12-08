<?php

namespace bviguier\MBuddy;

class Preset
{
    public function __construct(int $bankMSB, int $bankLSB, int $program)
    {
        $this->bankMSB = $bankMSB;
        $this->bankLSB = $bankLSB;
        $this->program = $program;
    }

    public function bankMSB(): int
    {
        return $this->bankMSB;
    }

    public function bankLSB(): int
    {
        return $this->bankLSB;
    }

    public function program(): int
    {
        return $this->program;
    }

    public function __toString(): string
    {
        return sprintf("%02X|%02X|%02X", $this->bankMSB, $this->bankLSB, $this->program);
    }

    private int $bankMSB;
    private int $bankLSB;
    private int $program;
}
