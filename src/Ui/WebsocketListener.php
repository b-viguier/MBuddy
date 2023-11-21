<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

interface WebsocketListener
{
    public function onMessage(string $message): void;
}
