<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core;

class Preset
{
    public function __construct(
        private Preset\Id $id,
        private string $name,
        private string $scoreTxt = '',
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
    
    public function scoreTxt(): string
    {
        return $this->scoreTxt;
    }

    public function with(
        ?Preset\Id $id = null,
        ?string $name = null,
        ?string $scoreTxt = null,
    ): self
    {
        return new self(
            $id ?? $this->id,
            $name ?? $this->name,
            $scoreTxt ?? $this->scoreTxt,
        );
    }
}
