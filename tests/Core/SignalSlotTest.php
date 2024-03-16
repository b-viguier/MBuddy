<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Core;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Core\Slot;
use PHPUnit\Framework\TestCase;

class SignalSlotTest extends TestCase
{
    public function testSignalCallSlotOnce(): void
    {
        $count = 0;
        $signal = new VariadicSignal();
        $slot = new VariadicSlot(function() use (&$count) {++$count;});

        $signal->connect($slot);

        $signal->emit();
        self::assertSame(1, $count);

        $signal->emit();
        self::assertSame(2, $count);
    }

    public function testSignalNotConnected(): void
    {
        $count = 0;
        $signal = new VariadicSignal();
        new VariadicSlot(function() use (&$count) {++$count;});

        $signal->emit();

        self::assertSame(0, $count);
    }

    public function testSignalConnectedToSeveralSlots(): void
    {
        $count1 = 0;
        $count2 = 0;
        $signal = new VariadicSignal();
        $slot1 = new VariadicSlot(function() use (&$count1) {++$count1;});
        $slot2 = new VariadicSlot(function() use (&$count2) {++$count2;});

        $signal->connect($slot1);
        $signal->connect($slot2);

        $signal->emit();
        self::assertSame(1, $count1);
        self::assertSame(1, $count2);
    }

    public function testSignalsChaining(): void
    {
        $count = 0;
        $signal1 = new VariadicSignal();
        $signal2 = new VariadicSignal();
        $slot = new VariadicSlot(function() use (&$count) {++$count;});

        $signal1->connect($signal2);
        $signal2->connect($slot);

        $signal1->emit();
        self::assertSame(1, $count);
    }

    public function testSignalDisconnectedWhenSlotDestroyed(): void
    {
        $count = 0;
        $function = function() use (&$count) {++$count;};

        $signal = new VariadicSignal();
        $slot = new VariadicSlot($function);

        $signal->connect($slot);

        $signal->emit();
        self::assertSame(1, $count);

        unset($slot);
        $signal->emit();
        self::assertSame(1, $count);

        $function();
        self::assertSame(2, $count);
    }

    public function testSignalDisconnected(): void
    {
        $count = 0;
        $signal = new VariadicSignal();
        $slot = new VariadicSlot(function() use (&$count) {++$count;});

        $signal->connect($slot);

        $signal->emit();
        self::assertSame(1, $count);

        $signal->disconnect($slot);
        $signal->emit();
        self::assertSame(1, $count);
    }

    public function testParametersAreForwarded(): void
    {
        $args = [];
        $signal = new VariadicSignal();
        $slot = new VariadicSlot(function(...$inputs) use (&$args) {$args = $inputs;});

        $expected = [1, "hello", new \stdClass()];
        $signal->connect($slot);
        $signal->emit(...$expected);

        self::assertSame($expected, $args);
    }

    public function testSignalWithMoreParametersThanSlot(): void
    {
        $args = [];
        $signal = new VariadicSignal();
        $slot = new VariadicSlot(function(int $a, string $b) use (&$args) {
            $args = [$a, $b];
        });

        $signal->connect($slot);
        $signal->emit(1, "hello", new \stdClass());

        self::assertSame([1, "hello"], $args);
    }
}

class VariadicSlot extends Slot
{
    public function __construct(
        \Closure $callback,
    ) {
        parent::__construct($callback);
    }

    public function call(mixed ...$args): void
    {
        $this->_call(...$args);
    }
}

class VariadicSignal extends Signal
{
    public function connect(Slot $slot): void
    {
        $this->_connect($slot);
    }

    public function emit(mixed ...$args): void
    {
        $this->_emit(...$args);
    }
}
