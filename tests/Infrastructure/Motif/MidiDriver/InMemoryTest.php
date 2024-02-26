<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Infrastructure\Motif\MidiDriver;

use Amp\Loop;
use Bveing\MBuddy\Infrastructure\Motif\MidiDriver;
use PHPUnit\Framework\TestCase;
use function Amp\call;
use function Amp\Promise\all;

class InMemoryTest extends TestCase
{
    public function testSendMessage(): void
    {
        Loop::run(function() {
            $driver = new MidiDriver\InMemory();
            $sequence = [];

            $this->assertNull($driver->popSentMessage());

            $sendPromise = call(function() use (&$driver, &$sequence) {
                $sequence[] = 'sending';
                yield $driver->send("My Message");
                $sequence[] = 'sent';
            });
            $sequence[] = 'popping';
            $message = $driver->popSentMessage();
            $sequence[] = 'popped';

            $this->assertSame('My Message', $message);

            yield $sendPromise;

            $this->assertSame(['sending', 'popping', 'sent', 'popped'], $sequence);
        });
    }

    public function testReceiveMessage(): void
    {
        Loop::run(function() {
            $driver = new MidiDriver\InMemory();
            $sequence = [];

            $receivePromises = [
                call(function() use (&$driver, &$sequence) {
                    $sequence[] = 'receiving1';
                    $message = yield $driver->receive();
                    $sequence[] = 'received1';

                    return $message;
                }),
                call(function() use (&$driver, &$sequence) {
                    $sequence[] = 'receiving2';
                    $message = yield $driver->receive();
                    $sequence[] = 'received2';

                    return $message;
                }),
            ];
            $sequence[] = 'pushing';
            $driver->pushReceivedMessage('My Message');
            $sequence[] = 'pushed';

            $messages = yield all($receivePromises);
            $this->assertSame(['My Message', 'My Message'], $messages);

            $this->assertSame(
                [
                'receiving1',
                'receiving2',
                'pushing',
                'received1',
                'received2',
                'pushed',
            ],
                $sequence,
            );
        });
    }

    public function testNotReceivingAlreadyPushedMessage(): void
    {
        $sequence = [];
        Loop::run(function() use (&$sequence) {
            $driver = new MidiDriver\InMemory();

            $sequence[] = 'pushing';
            $driver->pushReceivedMessage('My Message');
            $sequence[] = 'pushed';

            $receivePromise = call(function() use (&$driver, &$sequence) {
                $sequence[] = 'receiving';
                $message = yield $driver->receive();
                $sequence[] = 'received';

                return $message;
            });

            yield $receivePromise;
        });

        $this->assertSame(['pushing', 'pushed', 'receiving'], $sequence);
    }
}
