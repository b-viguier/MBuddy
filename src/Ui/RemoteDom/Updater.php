<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\RemoteDom;

interface Updater
{
    public function update(string $componentId, string $value): self;
}