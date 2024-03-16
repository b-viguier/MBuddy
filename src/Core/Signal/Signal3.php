<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Signal;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Core\Slot;

/**
 * @template T1
 * @template T2
 * @template T3
 */
final class Signal3 extends Signal
{
    /**
     * @param Slot\Slot0|Slot\Slot1<T1>|Slot\Slot2<T1,T2>|Slot\Slot3<T1,T2,T3>|Signal0|Signal1<T1>|Signal2<T1,T2>|Signal3<T1,T2,T3> $slot
     */
    public function connect(
        Slot\Slot0|Slot\Slot1|Slot\Slot2|Slot\Slot3|Signal0|Signal1|Signal2|Signal3 $slot
    ): void {
        parent::_connect($slot);
    }

    /**
     * @param T1 $arg1
     * @param T2 $arg2
     * @param T3 $arg3
     */
    public function emit(mixed $arg1, mixed $arg2, mixed $arg3): void
    {
        parent::_emit($arg1, $arg2, $arg3);
    }
}
