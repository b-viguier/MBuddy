<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure;

class ConsoleLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @inheritDoc
     * @param string $level
     * @param array<mixed> $context
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        echo sprintf(
            "[%s]\t%s (%s)\n",
            strtoupper($level),
            $message,
            json_encode($context, JSON_THROW_ON_ERROR),
        );
    }
}
