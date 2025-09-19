<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Async;

use Amp\Loop;
use Bveing\MBuddy\App\Runtime\BackgroundService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class LoopRunner
{
    public function __construct(
        private LoggerInterface $logger,
        private BackgroundService $backgroundService,
        private Server $server,
    ) {

    }

    /**
     * MUST be called first, even before creating LoopRunner instance
     */
    public static function globalInit(): void
    {
        \putenv('AMP_LOOP_DRIVER=' . NestableNativeDriver::class);
        \assert(Loop::get() instanceof NestableNativeDriver);
    }

    public function run(?Request $request): void
    {
        Loop::setErrorHandler(function(\Throwable $e) {
            $this->logger->critical("error handler -> " . $e->getMessage());
            $this->logger->critical("File " . $e->getFile() . " Line " . $e->getLine());
        });

        if (\extension_loaded("pcntl")) {
            \Amp\Loop::onSignal(\SIGINT, function(string $watcherId) {
                $this->logger->info('SIGINT received, stopping server...');
                \Amp\Loop::cancel($watcherId);
                $this->server->stop();
            });
        }

        Loop::run(function() use ($request): \Generator {
            $this->backgroundService->start();
            yield $this->server->run($request);
            $this->backgroundService->stop();

            Loop::stop();
        });
    }
}
