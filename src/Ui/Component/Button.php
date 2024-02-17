<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;
use Bveing\MBuddy\Ui\Id;

class Button implements Component
{
    use Trait\AutoId;
    use Trait\Childless;
    use trait\NonModifiable;

    /**
     * @param \Closure(string $value):void $onClick
     */
    public function __construct(
        private string $label,
        private \Closure $onClick,
    ) {
    }

    public function render(): string
    {
        return <<<HTML
            <button type="button" data-on-click="onClick" class="btn btn-primary" id="{$this->getId()}">
                {$this->label}
            </button>
            HTML;
    }

    public function onClick(): void
    {
        ($this->onClick)();
    }
}
