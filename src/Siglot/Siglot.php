<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

use Bveing\MBuddy\Siglot\Core\SignalMethod;
use Bveing\MBuddy\Siglot\Core\SlotMethod;

class Siglot
{
    /**
     * @param \Closure():Signal $signal
     * @param \Closure():mixed $slot
     */
    public static function connect0(\Closure $signal, \Closure $slot): void
    {
        self::connect(SignalMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @param \Closure():Signal $signal
     * @param \Closure():Signal $signalSlot
     */
    public static function chain0(\Closure $signal, \Closure $signalSlot): void
    {
        self::chain(SignalMethod::fromClosure($signal), SignalMethod::fromClosure($signalSlot));
    }

    /**
     * @template T1
     * @param \Closure(T1):Signal $signal
     * @param (\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    public static function connect1(\Closure $signal, \Closure $slot): void
    {
        self::connect(SignalMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @template T1
     * @param \Closure(T1):Signal $signal
     * @param (\Closure(T1):Signal)|(\Closure():Signal) $signalSlot
     */
    public static function chain1(\Closure $signal, \Closure $signalSlot): void
    {
        self::chain(SignalMethod::fromClosure($signal), SignalMethod::fromClosure($signalSlot));
    }

    /**
     * @template T1
     * @template T2
     * @param \Closure(T1,T2,mixed...):Signal $signal
     * @param (\Closure(T1,T2):mixed) $slot
     */
    public static function connect2(\Closure $signal, \Closure $slot): void
    {
        self::connect(SignalMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @template T1
     * @template T2
     * @param \Closure(T1,T2):Signal $signal
     * @param (\Closure(T1,T2):Signal)|(\Closure(T1):Signal)|(\Closure():Signal) $signalSlot
     */
    public static function chain2(\Closure $signal, \Closure $signalSlot): void
    {
        self::chain(SignalMethod::fromClosure($signal), SignalMethod::fromClosure($signalSlot));
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @param \Closure(T1,T2,T3,mixed...):Signal $signal
     * @param (\Closure(T1,T2,T3):mixed) $slot
     */
    public static function connect3(\Closure $signal, \Closure $slot): void
    {
        self::connect(SignalMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @param \Closure(T1,T2,T3):Signal $signal
     * @param (\Closure(T1,T2,T3):Signal)|(\Closure(T1,T2):Signal)|(\Closure(T1):Signal)|(\Closure():Signal) $signalSlot
     */
    public static function chain3(\Closure $signal, \Closure $signalSlot): void
    {
        self::chain(SignalMethod::fromClosure($signal), SignalMethod::fromClosure($signalSlot));
    }

    private static function connect(SignalMethod $signal, SlotMethod $slot): void
    {
        $emitter = $signal->object();
        $connector = $emitter->getConnector($signal);
        $connector->connect($slot);
    }

    private static function chain(SignalMethod $signalSrc, SignalMethod $signalDst): void
    {
        $emitter = $signalSrc->object();
        $connector = $emitter->getConnector($signalSrc);
        $receiver = $signalDst->object();
        $connector->chain($receiver->getConnector($signalDst));
    }
}
