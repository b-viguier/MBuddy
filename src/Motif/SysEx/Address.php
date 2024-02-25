<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

class Address
{
    public function __construct(
        private int $h,
        private int $m,
        private int $l,
    ) {
        \assert(0x00 <= $h && $h <= 0x47);
        \assert(0x00 <= $m && $m < 0x80);
        \assert(0x00 <= $l && $l < 0x80);
    }

    public function h(): int
    {
        return $this->h;
    }

    public function m(): int
    {
        return $this->m;
    }

    public function l(): int
    {
        return $this->l;
    }

    /**
     * @return array{int, int, int}
     */
    public function toArray(): array
    {
        return [$this->h, $this->m, $this->l];
    }

    public function toBinaryString(): string
    {
        return \pack('C3', $this->h, $this->m, $this->l);
    }

    public function equals(Address $other): bool
    {
        return $this->h === $other->h && $this->m === $other->m && $this->l === $other->l;
    }
}
