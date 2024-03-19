<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master\Repository;

use Amp\Promise;
use Amp\Success;
use Bveing\MBuddy\Motif\Master;

class InMemory implements Master\Repository
{
    public function __construct(Master\Id $currentId = null)
    {
        $this->currentId = $currentId ?? Master\Id::fromInt(0);
    }

    public function get(Master\Id $id): Promise
    {
        return new Success(
            $this->storage[$id->toInt()] ??= Master::default()->with(id: $id),
        );
    }

    public function set(Master $master): Promise
    {
        $this->storage[$master->id()->toInt()] = $master;

        return new Success();
    }

    public function currentMasterId(): Promise
    {
        return new Success($this->currentId);
    }

    public function setCurrentMasterId(Master\Id $masterId): Promise
    {
        $this->currentId = $masterId;

        return new Success(1);
    }

    private Master\Id $currentId;

    /** @var array<int,Master> */
    private array $storage = [];
}
