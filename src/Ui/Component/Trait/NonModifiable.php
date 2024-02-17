<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

trait NonModifiable
{
    public function isRefreshNeeded(): bool
    {
        return false;
    }
}
