<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Slot;

use Bveing\MBuddy\Core\Slot;

final class Slot0 extends Slot
{
    /**
     * @param \Closure(): mixed $callback
     */
    public function __construct(
        \Closure $callback,
    ) {
        parent::__construct($callback);
    }
}
