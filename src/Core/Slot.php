<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core;

class Slot
{
    protected function __construct(
        private \Closure $callback,
    ) {
    }

    protected function _call(mixed ...$args): void
    {
        ($this->callback)(...$args);
    }
}
