<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

use Bveing\MBuddy\Core\Signal;

trait Refreshable
{
    public function modified(): Signal\Signal0
    {
        return $this->modified ??= new Signal\Signal0();
    }

    private ?Signal\Signal0 $modified = null;

    private function refresh(): self
    {
        $this->modified()->emit(); // emit $this?

        return $this;
    }
}
