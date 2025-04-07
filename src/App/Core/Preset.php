<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core;

use Bveing\MBuddy\Motif\Master;

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
}
