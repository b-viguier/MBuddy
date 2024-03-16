<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core\Signal;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Core\Slot;

final class Signal0 extends Signal
{
    public function connect(Slot\Slot0|Signal0 $callback): void
    {
        parent::_connect($callback);
    }

    public function emit(): void
    {
        parent::_emit();
    }
}
