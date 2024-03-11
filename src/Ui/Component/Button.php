<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style\Color;
use Bveing\MBuddy\Ui\Template;

class Button implements Component
{
    use Trait\AutoId;
    use Trait\AutoVersion;

    /**
     * @param \Closure():void $onClick
     */
    public function __construct(
        private string $label,
        private \Closure $onClick,
    ) {
        $this->color = Color::PRIMARY();
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <button type="button" data-on-click="onClick" class="btn btn-{{ color }}" id="{{ id }}">
                {{ label }}
            </button>
            HTML,
            id: $this->id(),
            label: $this->label,
            color: $this->color,
        );
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

        return $this->refresh();
    }

    private Color $color;
}
