<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Ui\Websocket\Listener;
use Amp\Promise;

interface Websocket
{
    public function getPath(): string;

    public function send(string $message): Promise;

    public function setListener(Listener $listener): void;
}
