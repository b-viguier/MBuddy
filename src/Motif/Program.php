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
    }

    public function getBankMsb(): int
    {
        return $this->bankMsb;
    }

    public function getBankLsb(): int
    {
        return $this->bankLsb;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
