<?php

declare(strict_types=1);

namespace Bveing\Mbuddy\Infrastructure;

use Amp\Socket\SocketAddress;
use Amp\Socket\EncryptableSocket;
use Amp\Promise;

class UdpLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @return Promise<UdpLogger>
     */
    public static function create(
        SocketAddress $socketAddress,
    ): Promise {
        return \Amp\call(fn() => new self(
            yield \Amp\Socket\connect('udp://'.$socketAddress->toString()),
        ));
    }

    public function __construct(
        private EncryptableSocket $socket,
    ) {
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->socket->write(
            \sprintf(
                "[%s]:\t%s\n",
                $level,
                $message,
            ),
        );
    }
}
