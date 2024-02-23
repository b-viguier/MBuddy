<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysExClient;

use function Amp\asyncCall;

use Amp\Promise;
use Bveing\MBuddy\Motif\SysEx;
use Bveing\MBuddy\Motif\SysExClient;

class ConcurrencyLimiter implements SysExClient
{
    /** @var \SplQueue<\Closure> */
    private \SplQueue $queue;
    private int $nbRunning = 0;

    public function __construct(
        private SysExClient $sysExClient,
        private int $maxConcurrency,
    ) {
        $this->queue = new \SplQueue();
    }

    public function requestDump(SysEx\DumpRequest $request): Promise
    {
        $deferred = new \Amp\Deferred();
        $action = function() use ($request, $deferred) {
            $result = yield $this->sysExClient->requestDump($request);
            --$this->nbRunning;
            $deferred->resolve($result);

            if (!$this->queue->isEmpty()) {
                $next = $this->queue->dequeue();
                ++$this->nbRunning;
                asyncCall($next);
            }
        };

        if ($this->nbRunning >= $this->maxConcurrency) {
            $this->queue->enqueue($action);
        } else {
            ++$this->nbRunning;
            asyncCall($action);
        }

        return $deferred->promise();
    }

    public function sendDump(iterable $blocks): Promise
    {
        return $this->sysExClient->sendDump($blocks);
    }

    public function requestParameter(SysEx\ParameterRequest $request): Promise
    {
        $deferred = new \Amp\Deferred();
        $action = function() use ($request, $deferred) {
            ++$this->nbRunning;
            $result = yield $this->sysExClient->requestParameter($request);
            --$this->nbRunning;
            $deferred->resolve($result);

            if (!$this->queue->isEmpty()) {
                $next = $this->queue->dequeue();
                asyncCall($next);
            }
        };

        if ($this->nbRunning >= $this->maxConcurrency) {
            $this->queue->enqueue($action);
        } else {
            asyncCall($action);
        }

        return $deferred->promise();
    }

    public function sendParameter(SysEx\ParameterChange $change): Promise
    {
        return $this->sysExClient->sendParameter($change);
    }
}
