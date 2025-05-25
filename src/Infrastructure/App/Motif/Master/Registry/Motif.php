<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\App\Motif\Master\Registry;

use Amp\Promise;
use Bveing\MBuddy\App\Motif\Master\Registry;
use Bveing\MBuddy\Motif\Master;

class Motif implements Registry
{
    public function __construct(
        private Master\Repository $repository,
    ) {
    }

    public function get(Master\Id $id): ?Master
    {
        return null;
    }

    public function refresh(Master\Id $id): Promise
    {
        return $this->repository->get($id);
    }
}
