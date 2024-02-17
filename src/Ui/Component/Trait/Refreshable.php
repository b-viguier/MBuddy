<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

trait Refreshable
{
    private bool $refreshNeeded = true;

    public function isRefreshNeeded(): bool
    {
        return $this->refreshNeeded;
    }
}
