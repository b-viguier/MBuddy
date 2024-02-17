<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

interface Component
{
    public function render(): string;

    /**
     * @return iterable<Component>
     */
    public function getChildren(): iterable;

    public function getId(): Id;

    public function isRefreshNeeded(): bool;
}
