<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Ui\RemoteDom;

interface Component
{
    public function render(JsEventBus $jsEventBus): string;
}