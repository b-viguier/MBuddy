<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;

class ScoreViewer implements Component
{
    public function __construct(
        private JsEventBus $jsEventBus,
        private string $imagePath,
    ) {
    }

    public function render(): string
    {
        return <<<HTML
            <div class="row no-gutters">
                <div class="col">
                    <img src="{$this->imagePath}" class="img-thumbnail img-fluid" />
                </div>
            </div>
            HTML;
    }
}
