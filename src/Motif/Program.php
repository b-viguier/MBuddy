<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class Program
{
    public function __construct(
        private int $bankMsb,
        private int $bankLsb,
        private int $number,
    ) {
        assert(0 <= $bankMsb && $bankMsb <= 127);
        assert(0 <= $bankLsb && $bankLsb <= 127);
        assert(0 <= $number && $number <= 127);
    }

    public function bankMsb(): int
    {
        return $this->bankMsb;
    }

    public function bankLsb(): int
    {
        return $this->bankLsb;
    }

    public function number(): int
    {
        return $this->number;
    }
}
