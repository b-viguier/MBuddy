<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master\Repository\Decorator;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;

/**
 * Only for testing purposes, meant to be used with in memory repository,
 * to simulate network latency.
 */
class Delay implements Master\Repository
{
    public function __construct(
        private int $delayMs,
        private Master\Repository $delegate,
    ) {
    }

    public function get(Master\Id $id): Promise
    {
        return \Amp\call(function() use ($id) {
            yield \Amp\delay($this->delayMs);

            return yield $this->delegate->get($id);
        });
    }

    public function set(Master $master): Promise
    {
        return \Amp\call(function() use ($master) {
            yield \Amp\delay($this->delayMs);

            return yield $this->delegate->set($master);
        });
    }

    public function currentMasterId(): Promise
    {
        return \Amp\call(function() {
            yield \Amp\delay($this->delayMs);

            return yield $this->delegate->currentMasterId();
        });
    }

    public function setCurrentMasterId(Master\Id $id): Promise
    {
        return \Amp\call(function() use ($id) {
            yield \Amp\delay($this->delayMs);

            return yield $this->delegate->setCurrentMasterId($id);
        });
    }
}
