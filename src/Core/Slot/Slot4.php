<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Slot;

use Bveing\MBuddy\Core\Slot;

/**
 * @template T1
 * @template T2
 * @template T3
 * @template T4
 */
final class Slot4 extends Slot
{
    /**
     * @param \Closure(T1,T2,T3,T4): mixed $callback
     */
    public function __construct(
        \Closure $callback,
    ) {
        parent::__construct($callback);
    }
}
