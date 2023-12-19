<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class MasterId
{
    private function __construct(
        private int $id,
    ) {
        assert(-1 <= $id && $id <= 127);
    }

    public static function fromInt(int $id): self
    {
        assert(0 <= $id);

        return new self($id);
    }

    public static function editBuffer(): self
    {
        return new self(-1);
    }

    public function isEditBuffer(): bool
    {
        return $this->id === -1;
    }

    public function toInt(): int
    {
        return $this->id;
    }

    static public function getAll(): iterable
    {
        for ($i = 0; $i <= 127; $i++) {
            yield self::fromInt($i);
        }
    }
}
