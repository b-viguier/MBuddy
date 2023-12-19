<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

interface MidiDriver
{
    public function send(string $message): void;

    public function setListener(callable $listener): void;

    public function removeListener(callable $listener): void;
}
