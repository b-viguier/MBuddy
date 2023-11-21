<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\RemoteDom;

use Bveing\MBuddy\Ui\Component\Internal\Id;

class NullUpdater implements Updater
{
    public function update(Id $componentId, string $value): Updater
    {
        return $this;
    }
}
