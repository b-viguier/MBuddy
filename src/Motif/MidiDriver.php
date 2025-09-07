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

    public function addListener(MidiListener $listener): void;

    /**
     * @return Promise<null>
     */
    public function poll(): Promise;

    public function stopPolling(): void;
}
