<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure;

class ConsoleLogger extends \Psr\Log\AbstractLogger
{
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        echo sprintf(
            "[%s]\t%s (%s)\n",
            strtoupper($level),
            $message,
            var_export($context, true),
        );
    }
}
