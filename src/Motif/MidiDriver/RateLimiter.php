<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\MidiDriver;

use Amp\Deferred;
use Amp\Promise;
use Bveing\MBuddy\Motif\MidiDriver;
use function Amp\asyncCall;
use function Amp\delay;

class RateLimiter implements MidiDriver
{
    private float $nextAllowedTime = 0.0;

    private \Closure $microtimeFunction;

    /** @var \SplQueue<array{0: string, 1: Deferred<int>}> */
    private \SplQueue $queue;

    public function __construct(
        private MidiDriver $driver,
        private float $timeBetweenMessages,
        ?callable $microtimeFunction = null,
    ) {
        assert($timeBetweenMessages > 0, 'Time between messages must be greater than 0');
        $this->microtimeFunction = $microtimeFunction ? \Closure::fromCallable($microtimeFunction) : fn() => microtime(true);
        $this->queue = new \SplQueue();
    }

    public function send(string $message): Promise
    {
        $deferred = new Deferred();
        $this->queue->enqueue([$message, $deferred]);
        if (count($this->queue) === 1) {
            $this->scheduleNextMessage();
        }

        return $deferred->promise();
    }

    public function receive(): Promise
    {
        return $this->driver->receive();
    }

    private function scheduleNextMessage(): void
    {
        asyncCall(function(): \Generator {
            assert($this->queue->count() > 0);
            $timeToWait = $this->nextAllowedTime - ($this->microtimeFunction)();
            if ($timeToWait > 0) {
                yield delay(\intval($timeToWait * 1000));
            }
            [$message, $deferred] = $this->queue->bottom();
            $result = yield $this->driver->send($message);

            $this->queue->dequeue();
            $this->nextAllowedTime = ($this->microtimeFunction)() + $this->timeBetweenMessages;
            if (!$this->queue->isEmpty()) {
                $this->scheduleNextMessage();
            }

            $deferred->resolve($result);
        });
    }
}
