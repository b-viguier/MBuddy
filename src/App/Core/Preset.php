<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core;

use Bveing\MBuddy\Motif\Master;

class Preset
{
    public function __construct(
        private Master $master,
    ) {
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
