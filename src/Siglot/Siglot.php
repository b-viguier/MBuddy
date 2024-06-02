<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

use Bveing\MBuddy\Siglot\Core\SlotMethod;

class Siglot
{
    /**
     * @param \Closure():Signal $signal
     * @param \Closure():mixed $slot
     */
    static public function connect0(\Closure $signal, \Closure $slot): void
    {
        self::connect(SlotMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @template T1
     * @param \Closure(T1):Signal $signal
     * @param (\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    static public function connect1(\Closure $signal, \Closure $slot): void
    {
        self::connect(SlotMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @template T1
     * @template T2
     * @param \Closure(T1,T2,mixed...):Signal $signal
     * @param (\Closure(T1,T2):mixed) $slot
     */
    static public function connect2(\Closure $signal, \Closure $slot): void
    {
        self::connect(SlotMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @param \Closure(T1,T2,T3,mixed...):Signal $signal
     * @param (\Closure(T1,T2,T3):mixed) $slot
     */
    static public function connect3(\Closure $signal, \Closure $slot): void
    {
        self::connect(SlotMethod::fromClosure($signal), SlotMethod::fromClosure($slot));
    }

    static private function connect(SlotMethod $signal, SlotMethod $slot): void
    {
        \assert($signal->isSignal());
        $emitter = $signal->object();
        \assert($emitter instanceof Emitter);
        $connector = $emitter->getSignalConnector($signal);
        if ($slot->isSignal()) {
            $otherEmitter = $slot->object();
            \assert($otherEmitter instanceof Emitter);
            $connector->chain($otherEmitter->getSignalConnector($slot));
        } else {
            $connector->connect($slot);
        }
    }
}
