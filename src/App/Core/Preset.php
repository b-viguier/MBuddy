<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core;

use Bveing\MBuddy\Motif\Master;

class Preset
{
    public static function default(): self
    {
        return new self(Master::default());
    }

    public function __construct(
        private Master $master,
    ) {
    }

    public function id(): Preset\Id
    {
        return Preset\Id::fromMasterId($this->master->id());
    }

    public function name(): string
    {
        return $this->master->name();
    }

    public function master(): Master
    {
        return $this->master;
    }

    public function score(): string
    {
        return ''; //TODO
    }
}
