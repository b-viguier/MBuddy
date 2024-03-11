<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Ui\Rendering\Template;

interface Component
{
    public function template(): Template;

    public function id(): Id;

    public function version(): int;
}
