<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

trait Refreshable
{
    public function isRefreshNeeded(): bool
    {
        return $this->refreshNeeded;
    }
    private bool $refreshNeeded = true;
}
