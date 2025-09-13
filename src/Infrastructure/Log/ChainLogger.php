<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ChainLogger extends AbstractLogger
{
    public function __construct(LoggerInterface ...$loggers)
    {
        $this->loggers = $loggers;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }

    private array $loggers;
}
