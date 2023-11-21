<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Websocket;

class NullListener implements Listener
{
    public function onMessage(string $message): void
    {
    }
}
