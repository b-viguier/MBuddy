<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Amp\Promise;
use Bveing\MBuddy\Ui\Websocket\Listener;

interface Websocket
{
    public function path(): string;

    /**
     * @return Promise<null>
     */
    public function send(string $message): Promise;

    public function setListener(Listener $listener): void;
}
