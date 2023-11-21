<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\RemoteDom;

class NullUpdater implements Updater
{
    public function update(string $componentId, string $value): Updater
    {
        return $this;
    }
}
