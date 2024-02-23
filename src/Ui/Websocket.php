<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Amp\Promise;
use Bveing\MBuddy\Ui\Websocket\Listener;

interface Websocket
{
    public function getPath(): string;

    /**
     * @return Promise<null>
     */
    public function send(string $message): Promise;

    public function setListener(Listener $listener): void;
}
