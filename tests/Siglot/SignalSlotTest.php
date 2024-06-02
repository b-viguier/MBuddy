<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Siglot;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;
use Bveing\MBuddy\Siglot\Siglot;

class SignalSlotTest extends TestCase
{
    public function testSignalCallSlotOnce(): void
    {
        $emitter = new SpyEmitter();
        $receiver = new SpyReceiver();

        Siglot::connect0(
            $this->func($emitter, 'signal'),
            $this->func($receiver, 'slot')
        );

        self::assertCount(0, $receiver->calls);
        $emitter->doEmit();
        self::assertCount(1, $receiver->calls);

        $emitter->doEmit();
        self::assertCount(2, $receiver->calls);
    }

    public function testSignalNotConnected(): void
    {
        $emitter = new SpyEmitter();
        $receiver = new SpyReceiver();


        self::assertCount(0, $receiver->calls);
        $emitter->doEmit();
        self::assertCount(0, $receiver->calls);
    }

    public function testSignalConnectedToSeveralSlots(): void
    {
        $emitter = new SpyEmitter();
        $receiver1 = new SpyReceiver();
        $receiver2 = new SpyReceiver();

        Siglot::connect0(
            $this->func($emitter, 'signal'),
            $this->func($receiver1, 'slot')
        );
        Siglot::connect0(
            $this->func($emitter, 'signal'),
            $this->func($receiver2, 'slot')
        );

        $emitter->doEmit();
        self::assertCount(1, $receiver1->calls);
        self::assertCount(1, $receiver2->calls);
    }

    public function testSignalsChaining(): void
    {
        $emitter1 = new SpyEmitter();
        $emitter2 = new SpyEmitter();
        $receiver = new SpyReceiver();

        Siglot::connect0(
            $this->func($emitter1, 'signal'),
            $this->func($emitter2, 'signal')
        );
        Siglot::connect0(
            $this->func($emitter2, 'signal'),
            $this->func($receiver, 'slot')
        );

        $emitter1->doEmit();
        self::assertCount(1, $receiver->calls);
    }

    public function testSignalDisconnectedWhenSlotDestroyed(): void
    {
        $emitter = new SpyEmitter();
        $count = 0;
        $receiver = new class(function() use (&$count) {++$count;}) {
            public function __construct(private \Closure $callback) {}
            public function slot(): void
            {
                ($this->callback)();
            }
        };

        Siglot::connect0(
            $this->func($emitter, 'signal'),
            $this->func($receiver, 'slot')
        );

        self::assertSame(0, $count);
        $emitter->doEmit();
        self::assertSame(1, $count);

        unset($receiver);
        $emitter->doEmit();
        self::assertSame(1, $count);
    }

//    public function testSignalDisconnected(): void
//    {
//        $count = 0;
//        $signal = new VariadicSignal();
//        $slot = new VariadicSlot(function() use (&$count) {++$count;});
//
//        $signal->connect($slot);
//
//        $signal->emit();
//        self::assertSame(1, $count);
//
//        $signal->disconnect($slot);
//        $signal->emit();
//        self::assertSame(1, $count);
//    }

    public function testParametersAreForwarded(): void
    {
        $emitter = new SpyEmitter();
        $receiver = new SpyReceiver();
        $expected = [1, "hello", new \stdClass()];

        Siglot::connect3(
            $this->func($emitter, 'signal'),
            $this->func($receiver, 'slot')
        );

        $emitter->doEmit(...$expected);
        self::assertSame([$expected], $receiver->calls);
    }

    public function testSignalWithMoreParametersThanSlot(): void
    {
        $args = [];
        $emitter = new SpyEmitter();
        $receiver = new class(function(int $a, string $b) use (&$args) {$args = [$a, $b];}) {
            public function __construct(private \Closure $callback) {}
            public function slot(int $a, string $b): void
            {
                ($this->callback)($a, $b);
            }
        };

        Siglot::connect3(
            $this->func($emitter, 'signal'),
            $this->func($receiver, 'slot')
        );

        $emitter->doEmit(1, "hello", new \stdClass());
        self::assertSame([1, "hello"], $args);
    }

    public function testSeveralSignalsToSameSlot(): void
    {
        $emitter1 = new SpyEmitter();
        $emitter2 = new SpyEmitter();
        $receiver = new SpyReceiver();

        Siglot::connect0(
            $this->func($emitter1, 'signal'),
            $this->func($receiver, 'slot')
        );
        Siglot::connect0(
            $this->func($emitter2, 'signal'),
            $this->func($receiver, 'slot')
        );

        $emitter1->doEmit();
        self::assertCount(1, $receiver->calls);

        $emitter2->doEmit();
        self::assertCount(2, $receiver->calls);
    }

    private function func(object $obj, string $method): \Closure
    {
        $callable = [$obj, $method];
        \assert(\is_callable($callable));
        return \Closure::fromCallable($callable);
    }
}

class SpyReceiver
{
    /** @var array<mixed[]> */
    public array $calls = [];

    public function slot(mixed ...$args): void
    {
        $this->calls[] = $args;
    }
}

class SpyEmitter implements Emitter
{
    use EmitterHelper;

    public function signal(mixed ...$args): Signal
    {
        return Signal::auto();
    }

    public function doEmit(mixed ...$args): void
    {
        $this->emit($this->signal(...$args));
    }
}
