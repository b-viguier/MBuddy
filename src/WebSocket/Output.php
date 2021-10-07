<?php

namespace bviguier\MBuddy\WebSocket;

use bviguier\RtMidi\Message;

class Output implements \bviguier\RtMidi\Output
{
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function name(): string
    {
        return 'WS Output';
    }

    public function send(Message $message): void
    {
        $this->server->sendBinary($message->toBinString());
    }

    private Server $server;
}
