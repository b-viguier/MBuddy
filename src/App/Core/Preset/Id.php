<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core\Preset;

use Bveing\MBuddy\Motif\Master;

class Id implements \Stringable
{
    public static function new(): self
    {
        return new self(uniqid('pId', true));
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function __construct(private string $value)
    {
    }
}
