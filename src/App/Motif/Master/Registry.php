<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Motif\Master;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;

interface Registry
{
    public function get(Master\Id $id): ?Master;

    /**
     * @return Promise<?Master>
     */
    public function refresh(Master\Id $id): Promise;
}
