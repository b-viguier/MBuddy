<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core;

class Preset
{
    public function __construct(
        private Preset\Id $id,
        private string $name,
    ) {
    }

    public function id(): Preset\Id
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function with(
        ?string $name = null,
    ): self
    {
        return new self(
            $this->id,
            $name ?? $this->name,
        );
    }
}
