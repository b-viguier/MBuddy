<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Infrastructure\Motif\MidiDriver;

use Amp\Loop;
use Bveing\MBuddy\Infrastructure\Motif\MidiDriver;
use Bveing\MBuddy\Infrastructure\Motif\MidiListener;
use PHPUnit\Framework\TestCase;
use function Amp\call;

class InMemoryTest extends TestCase
{
    public function testSendMessage(): void
    {
        Loop::run(function() {
            $driver = new MidiDriver\InMemory();
            $sequence = [];

            self::assertNull($driver->popSentMessage());

            $sendPromise = call(function() use (&$driver, &$sequence) {
                $sequence[] = 'sending';
                yield $driver->send("My Message");
                $sequence[] = 'sent';
            });
            $sequence[] = 'popping';
            $message = $driver->popSentMessage();
            $sequence[] = 'popped';

            self::assertSame('My Message', $message);

            yield $sendPromise;

            self::assertSame(['sending', 'popping', 'sent', 'popped'], $sequence);
        });
    }

    public function testDispatchingMessage(): void
    {
        Loop::run(function() {
            $driver = new MidiDriver\InMemory();
            $listener1 = new MidiListener\InMemory();
            $listener2 = new MidiListener\InMemory();
            $driver->addListener($listener1);
            $driver->addListener($listener2);

            $driver->pushReceivedMessage('My Message');

            self::assertSame(['My Message'], $listener1->messages);
            self::assertSame(['My Message'], $listener2->messages);
        });
    }
}
