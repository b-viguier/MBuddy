<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

interface Websocket
{
    public function getUri(): string;

    public function send(string $message): void;

    public function setListener(WebsocketListener $listener): void;
}
