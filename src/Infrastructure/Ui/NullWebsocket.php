<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Ui;

use Amp\Promise;
use Amp\Success;
use Bveing\MBuddy\Ui\Websocket;

class NullWebsocket implements Websocket
{
    public function getPath(): string
    {
        return '/';
    }

    public function send(string $message): Promise
    {
        return new Success();
    }

    public function setListener(Websocket\Listener $listener): void
    {
    }
}
