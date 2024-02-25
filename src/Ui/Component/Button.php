<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style\Color;

class Button implements Component
{
    use Trait\AutoId;
    use Trait\Childless;
    use Trait\Refreshable;

    /**
     * @param \Closure():void $onClick
     */
    public function __construct(
        private string $label,
        private \Closure $onClick,
    ) {
        $this->color = Color::PRIMARY();
    }

    public function render(): string
    {
        $this->refreshNeeded = false;
        return <<<HTML
            <button type="button" data-on-click="onClick" class="btn btn-{$this->color}" id="{$this->id()}">
                {$this->label}
            </button>
            HTML;
    }

    public function onClick(): void
    {
        ($this->onClick)();
    }

    public function set(
        ?string $label = null,
        ?Color $color = null,
    ): self {
        $this->label = $label ?? $this->label;
        $this->color = $color ?? $this->color;
        $this->refreshNeeded = true;

        return $this;
    }

    private Color $color;
}
