<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\RemoteDom;

interface Renderer
{
    public function jsEventSender(string $componentId, string $eventId, \Closure $onEvent, string $jsEventSerializer): self;

    public function jsUpdater(string $componentId, string $jsUpdater): self;
}
