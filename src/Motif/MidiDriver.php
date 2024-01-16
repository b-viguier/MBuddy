<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

use Amp\Promise;

interface MidiDriver
{
    /**
     * @param string $message
     * @return Promise<int>
     */
    public function send(string $message): Promise;

    public function setListener(callable $listener): void;

    public function removeListener(callable $listener): void;
}
