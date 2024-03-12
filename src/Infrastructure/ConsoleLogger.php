<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure;

class ConsoleLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @param array<mixed> $context
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        \assert(\is_string($level));

        echo \sprintf(
            "[%s]\t%s (%s)\n",
            \strtoupper($level),
            $message,
            \json_encode($context, \JSON_THROW_ON_ERROR),
        );
    }
}
