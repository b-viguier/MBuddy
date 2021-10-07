<?php

namespace bviguier\MBuddy\WebSocket;

use bviguier\RtMidi\Exception\MidiException;
use bviguier\RtMidi\Message;

class Input implements \bviguier\RtMidi\Input
{
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function name(): string
    {
        return 'WS Output';
    }

    public function allow(int $allowMask): void
    {
        // Not implemented
    }

    public function pullMessage(): ?Message
    {
        if( null !== ($msg = $this->server->read())) {
            return Message::fromBinString($msg);
        }

        return null;
    }

    private Server $server;
}
