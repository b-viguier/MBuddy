<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style\Color;
use Bveing\MBuddy\Ui\Style\Icon;
use Bveing\MBuddy\Ui\Template;

class Button implements Component
{
    use Trait\AutoId;
    use Trait\AutoVersion;

    public static function create(): Button
    {
        return new self(
            label: '',
            color: Color::PRIMARY(),
            icon: null,
            signalOnClick: new Signal\Signal0(),
        );
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <button type="button" data-on-click="onClick" class="btn btn-{{ color }}" id="{{ id }}">
                {{ icon }}{{ label }}
            </button>
            HTML,
            id: $this->id(),
            label: $this->label,
            color: $this->color,
            icon: $this->icon?->html(),
        );
    }

    public function onClick(): void
    {
        $this->signalOnClick->emit();
    }

    public function set(
        ?string $label = null,
        ?Color $color = null,
        Icon|null|false $icon = false,
    ): self {
        $this->label = $label ?? $this->label;
        $this->color = $color ?? $this->color;
        $this->icon = $icon === false ? $this->icon : $icon;

        return $this->refresh();
    }


    private function __construct(
        private string $label,
        private Color $color,
        private ?Icon $icon,
        public Signal\Signal0 $signalOnClick,
    ) {
    }
}
