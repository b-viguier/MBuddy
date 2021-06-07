<?php

namespace bviguier\MBuddy\WebSocket;

class Server
{
    static public function createFromClient(string $address, int $port, int $timeout): ?self
    {
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($server, $address, $port);
        socket_listen($server);
        socket_set_nonblock($server);
        $maxTime = time() + $timeout;
        while (time() <= $maxTime && false === $client = socket_accept($server)) {
            usleep(100000);
        }
        if ($client === false) {
            return null;
        }

        // Send WebSocket handshake headers.
        $request = socket_read($client, 5000);
        preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
        $key = base64_encode(pack(
            'H*',
            sha1($matches[1].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
        ));
        $headers = "HTTP/1.1 101 Switching Protocols\r\n";
        $headers .= "Upgrade: websocket\r\n";
        $headers .= "Connection: Upgrade\r\n";
        $headers .= "Sec-WebSocket-Version: 13\r\n";
        $headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
        socket_write($client, $headers, strlen($headers));

        return new self($server, $client);
    }

    public function send(string $message): void
    {
        socket_write($this->client, chr(129).chr(strlen($message)).$message);
    }

    /** @var resource */
    private $server;
    /** @var resource */
    private $client;

    private function __construct($server, $client)
    {
        $this->server = $server;
        $this->client = $client;
    }
}
