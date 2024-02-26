<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Amp\Deferred;
use Amp\Promise;
use Bveing\MBuddy\Motif\MidiDriver;

class InMemory implements MidiDriver
{
    /** @var \SplQueue<array{0:string,1:Deferred<null>}> */
    private \SplQueue $sendQueue;

    /** @var Deferred<string>  */
    private Deferred $receiveDeferred;

    public function __construct()
    {
        $this->sendQueue = new \SplQueue();
        $this->receiveDeferred = new Deferred();
    }

    public function send(string $message): Promise
    {
        $deferred = new Deferred();
        $this->sendQueue->push([$message, $deferred]);

        return $deferred->promise();
    }

    public function receive(): Promise
    {
        return $this->receiveDeferred->promise();
    }

    public function popSentMessage(): ?string
    {
        if ($this->sendQueue->isEmpty()) {
            return null;
        }

        [$message, $deferred] = $this->sendQueue->shift();
        $deferred->resolve();

        return $message;
    }

    public function pushReceivedMessage(string $message): void
    {
        $deferred = $this->receiveDeferred;
        $this->receiveDeferred = new Deferred();
        $deferred->resolve($message);
    }
}
