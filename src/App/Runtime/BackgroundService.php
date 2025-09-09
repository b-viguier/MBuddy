<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Runtime;

class BackgroundService
{
    public function start(): void
    {
        foreach ($this->services as ['service' => $service, 'start' => $startFn, 'stop' => $stopFn]) {
            if($startFn !== null) {
                $startFn();
            }
        }
    }

    public function stop(): void
    {
        foreach ($this->services as ['service' => $service, 'start' => $startFn, 'stop' => $stopFn]) {
            if($stopFn !== null) {
                $stopFn();
            }
        }
    }

    public function addService(object $service, ?string $startFn, ?string $stopFn): void
    {
        $this->services[] = [
            'service' => $service,
            'start' => $this->callable($service, $startFn),
            'stop' => $this->callable($service, $stopFn),
        ];
    }

    /** @var array<array{service:object, start:callable|null, stop:callable|null}> */
    private array $services = [];

    private function callable(object $service, ?string $fn): ?callable
    {
        $callable = [$service, $fn];
        if ($fn === null || !\is_callable($callable)) {
            return null;
        }

        return $callable;
    }
}
