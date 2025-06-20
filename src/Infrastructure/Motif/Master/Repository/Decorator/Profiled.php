<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\Master\Repository\Decorator;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;
use Symfony\Component\Stopwatch\Stopwatch;

class Profiled implements Master\Repository
{
    public function __construct(
        private Master\Repository $repository,
        private Stopwatch $stopwatch,
    ) {
    }

    public function get(Master\Id $id): Promise
    {
        return $this->profile(
            'master.get',
            fn() => $this->repository->get($id),
        );
    }

    public function set(Master $master): Promise
    {
        return $this->profile(
            'master.set',
            fn() => $this->repository->set($master),
        );
    }

    public function currentMasterId(): Promise
    {
        return $this->profile(
            'master.currentMasterId',
            fn() => $this->repository->currentMasterId(),
        );
    }

    public function setCurrentMasterId(Master\Id $id): Promise
    {
        return $this->profile(
            'master.setCurrentMasterId',
            fn() => $this->repository->setCurrentMasterId($id),
        );
    }

    /**
     * @template T
     * @param callable():Promise<T> $callable
     * @return Promise<T>
     */
    private function profile(string $name, callable $callable): Promise
    {
        $this->stopwatch->start($name, 'motif');
        $promise = $callable();
        $promise->onResolve(function() use ($name) {
            $this->stopwatch->stop($name);
        });

        return $promise;
    }
}
