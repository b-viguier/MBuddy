<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

use Bveing\MBuddy\Siglot\Signal;

trait Refreshable
{
    public function modified(): Signal
    {
        return Signal::auto();
    }


    private function refresh(): void
    {
        $this->emit($this->modified());
    }

    abstract private function emit(Signal $signal): void;
}
