<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver\Decorator;

use Amp\Promise;
use Bveing\MBuddy\Motif\MidiDriver;
use Bveing\MBuddy\Motif\MidiListener;
use Symfony\Component\Stopwatch\Stopwatch;

class Profiled implements MidiDriver
{
    public function __construct(
        private MidiDriver $driver,
        private Stopwatch $stopwatch,
    ) {
    }

    public function send(string $message): Promise
    {
        $this->stopwatch->start('send', 'midi');
        $promise = $this->driver->send($message);
        $promise->onResolve(function() {
            $this->stopwatch->stop('send');
        });

        return $promise;
    }

    public function addListener(MidiListener $listener): void
    {
        $this->driver->addListener($listener);
    }

    public function poll(): Promise
    {
        return $this->driver->poll();
    }

    public function stopPolling(): void
    {
        $this->driver->stopPolling();
    }
}
