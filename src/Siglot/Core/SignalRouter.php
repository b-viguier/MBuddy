<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

use Bveing\MBuddy\Siglot\Signal;

class SignalRouter
{
    public function __construct()
    {
        $this->connections = new \ArrayObject();
    }

    public function getConnector(SignalMethod $signal): Connector
    {
        $connection = $this->connections[$signal->name()] ?? $this->connections[$signal->name()] = new Connection($signal, new SlotCollection());

        return new Connector($connection->signal, $connection->slots);
    }

    public function emit(Signal $signal): void
    {
        \assert(isset($this->connections[$signal->method()]));
        $this->connections[$signal->method()]->slots->invoke($signal->args());
    }
    /** @var \ArrayObject<string,Connection> $connections */
    private \ArrayObject $connections;
}
