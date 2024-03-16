<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Slot;

use Bveing\MBuddy\Core\Slot;

/**
 * @template T1
 */
final class Slot1 extends Slot
{
    /**
     * @param \Closure(T1): mixed $callback
     */
    public function __construct(
        \Closure $callback,
    ) {
        parent::__construct($callback);
    }
}
