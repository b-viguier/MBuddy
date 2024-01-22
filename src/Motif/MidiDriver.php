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

    /**
     * @return Promise<?string> Resolves to null if connection is closed
     */
    public function receive(): Promise;
}
