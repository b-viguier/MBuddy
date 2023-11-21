<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\RemoteDom;

use Bveing\MBuddy\Ui\Component\Internal\Id;

interface Updater
{
    public function update(Id $componentId, string $value): self;
}