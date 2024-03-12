<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure;

use Amp\Promise;
use Amp\Socket\EncryptableSocket;
use Amp\Socket\SocketAddress;

class UdpLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @return Promise<UdpLogger>
     */
    public static function create(
        SocketAddress $socketAddress,
    ): Promise {
        return \Amp\call(fn(): \Generator => new self(
            yield \Amp\Socket\connect('udp://'.$socketAddress->toString()),
        ));
    }

    public function __construct(
        private EncryptableSocket $socket,
    ) {
    }

    /**
     * @param array<mixed> $context
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        \assert(\is_string($level));

        $this->socket->write(
            \sprintf(
                "[%s]:\t%s\n",
                $level,
                $message,
            ),
        );
    }
}
