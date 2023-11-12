<?php

declare(strict_types=1);

namespace Bveing\Mbuddy\Infrastructure;

use Amp\Socket\SocketAddress;
use Amp\Loop;

class UdpLogger extends \Psr\Log\AbstractLogger
{
    /** @var resource */
    private $socket;

    public function __construct(
        SocketAddress $socketAddress,
    ) {
        if (!$this->socket = \stream_socket_client(
            'udp://'.$socketAddress->toString(),
            $errno,
            $errstr,
            0,
            STREAM_CLIENT_ASYNC_CONNECT,
        )) {
            throw new \Exception(
                \sprintf(
                    "Connection to %s failed: [Error #%d] %s",
                    $socketAddress->toString(),
                    $errno,
                    $errstr,
                ),
            );
        }
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        Loop::onWritable(
            $this->socket,
            function (string $watcherId, $socket, $data) {
                @\fwrite(
                    $this->socket,
                    $data,
                );
                Loop::disable($watcherId);
            },
            \sprintf(
                "[%s]:\t%s\n",
                $level,
                $message,
            ),
        );
    }
}
