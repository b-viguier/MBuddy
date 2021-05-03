<?php

namespace bviguier\MBuddy;

class SongId
{
    const DEFAULT_ID = 0;

    public function __construct(int $id)
    {
        assert($id >= 0 && $id < 100);
        $this->id = $id;
    }

    static public function first(): self
    {
        return new self(1);
    }

    static public function default(): self
    {
        return new self(self::DEFAULT_ID);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function next(): self
    {
        return $this->id < 99 ? new self($this->id + 1) : $this;
    }

    public function previous(): self
    {
        return $this->id >= 1 ? new self($this->id - 1) : $this;
    }

    public function __toString(): string
    {
        return sprintf("%02d", $this->id);
    }

    private int $id;
}
