<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

class SignalManager
{
    /** @var \ArrayObject<string,Connection> $connections */
    private \ArrayObject $connections;

    public function __construct()
    {
        $this->connections = new \ArrayObject();
    }

    public function getConnector(SlotMethod $signal): Connector
    {
        $connection = $this->connections[$signal->name()] ?? $this->connections[$signal->name()] = new Connection($signal, new SlotCollection());

        return new Connector($connection->signal, $connection->slots);
    }

    /**
     * @param array<mixed> $args
     */
    public function emit(string $signalName, array $args): void
    {
        \assert(isset($this->connections[$signalName]));
        $this->connections[$signalName]->slots->invoke($args);
    }
}
