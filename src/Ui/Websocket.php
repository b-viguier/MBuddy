<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Ui\Websocket\Listener;

interface Websocket
{
    public function getPath(): string;

    public function send(string $message): void;

    public function setListener(Listener $listener): void;
}
