<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

trait EventDispatcherTrait
{
    private array $listeners = [];

    public function setListener(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function removeListener(callable $listener): void
    {
        $this->listeners = array_filter(
            $this->listeners,
            fn(callable $l) => $l !== $listener
        );
    }

    private function dispatch(string $data): void
    {
        $listeners = $this->listeners;
        foreach ($listeners as $listener) {
            if ($listener($data)) {
                break;
            }
        }
    }
}
