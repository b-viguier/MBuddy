<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Slot;

use Bveing\MBuddy\Core\Slot;

/**
 * @template T1
 * @template T2
 * @template T3
 */
final class Slot3 extends Slot
{
    /**
     * @param \Closure(T1,T2,T3): mixed $callback
     */
    public function __construct(
        \Closure $callback,
    ) {
        parent::__construct($callback);
    }
}
