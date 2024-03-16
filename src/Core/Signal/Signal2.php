<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Signal;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Core\Slot;

/**
 * @template T1
 * @template T2
 */
final class Signal2 extends Signal
{
    /**
     * @param Slot\Slot0|Slot\Slot1<T1>|Slot\Slot2<T1,T2>|Signal0|Signal1<T1>|Signal2<T1,T2> $slot
     */
    public function connect(
        Slot\Slot0|Slot\Slot1|Slot\Slot2|Signal0|Signal1|Signal2 $slot
    ): void {
        parent::_connect($slot);
    }

    /**
     * @param T1 $arg1
     * @param T2 $arg2
     */
    public function emit(mixed $arg1, mixed $arg2): void
    {
        parent::_emit($arg1, $arg2);
    }
}
