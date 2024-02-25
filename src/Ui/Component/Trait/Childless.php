<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

trait Childless
{
    public function children(): iterable
    {
        return [];
    }
}
