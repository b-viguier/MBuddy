<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver\Decorator;

use Amp\Promise;
use Bveing\MBuddy\Motif\MidiDriver;
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

    public function receive(): Promise
    {
        $this->stopwatch->start('midi.receive', 'midi');
        $promise = $this->driver->receive();
        $promise->onResolve(function() {
            $this->stopwatch->stop('midi.receive');
        });

        return $promise;
    }
}
