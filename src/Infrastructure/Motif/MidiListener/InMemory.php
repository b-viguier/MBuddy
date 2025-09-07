<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiListener;

use Bveing\MBuddy\Motif\MidiListener;

class InMemory implements MidiListener
{
    /** @var array<string> */
    public array $messages = [];

    public function onMidiMessage(string $message): bool
    {
        $this->messages[] = $message;
        return true;
    }
}
