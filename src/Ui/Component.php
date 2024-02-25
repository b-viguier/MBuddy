<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

interface Component
{
    public function render(): string;

    /**
     * @return iterable<Component>
     */
    public function children(): iterable;

    public function id(): Id;

    public function isRefreshNeeded(): bool;
}
