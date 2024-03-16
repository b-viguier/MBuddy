<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Signal;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Core\Slot;

/**
 * @template T1
 */
final class Signal1 extends Signal
{
    /**
     * @param Slot\Slot0|Slot\Slot1<T1>|Signal0|Signal1<T1> $slot
     */
    public function connect(
        Slot\Slot0|Slot\Slot1|Signal0|Signal1 $slot
    ): void {
        parent::_connect($slot);
    }

    /**
     * @param T1 $arg1
     */
    public function emit(mixed $arg1): void
    {
        parent::_emit($arg1);
    }
}
