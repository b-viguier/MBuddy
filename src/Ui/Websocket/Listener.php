<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Websocket;

interface Listener
{
    public function onMessage(string $message): void;
}
