<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core;

use Bveing\MBuddy\Motif\Master;

class Preset
{
    public function __construct(
        private Preset\Id $id,
        private string $name,
        private ?Master\Id $masterId = null,
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

    public function masterId(): ?Master\Id
    {
        return $this->masterId;
    }

    public function with(
        ?Preset\Id $id = null,
        ?string $name = null,
        Master\Id|null|false $masterId = false,
        ?string $scoreTxt = null,
    ): self {
        return new self(
            $id ?? $this->id,
            $name ?? $this->name,
            $masterId === false ? $this->masterId : $masterId,
            $scoreTxt ?? $this->scoreTxt,
        );
    }
}
