<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\SinglePageApp;

interface Renderer
{
    public function scheduleRefresh(): void;
}
