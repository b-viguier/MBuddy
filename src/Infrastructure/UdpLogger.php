<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure;

use Amp\Promise;
use Amp\Socket\EncryptableSocket;

class UdpLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @return Promise<UdpLogger>
     */
    public static function createAsync(
        string $socketAddress,
    ): Promise {
        return \Amp\call(fn(): \Generator => new self(
            yield \Amp\Socket\connect('udp://'.$socketAddress),
        ));
    }

    public static function create(
        string $socketAddress,
    ): self {
        return Promise\wait(self::createAsync($socketAddress));
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
                "[%s]:\t%s %s\n",
                $level,
                $message,
                \json_encode($context)
            ),
        );
    }
}
