<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\MidiDriver;

use Bveing\MBuddy\Motif\MidiListener;

trait DispatcherTrait
{
    public function addListener(MidiListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    private function dispatch(string $message): void
    {
        foreach ($this->listeners as $listener) {
            $listener->onMidiMessage($message);
        }
    }

    /** @var array<MidiListener> */
    private array $listeners = [];
}
