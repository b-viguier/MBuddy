<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\RemoteDom;

use Bveing\MBuddy\Ui\Component\Internal\Id;

interface Renderer
{
    public function jsEventSender(Id $componentId, string $eventId, \Closure $onEvent, string $jsEventSerializer): self;

    public function jsUpdater(Id $componentId, string $jsUpdater): self;
}
