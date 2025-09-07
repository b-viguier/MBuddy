<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

interface MidiListener
{
    public function onMidiMessage(string $message): bool;
}
