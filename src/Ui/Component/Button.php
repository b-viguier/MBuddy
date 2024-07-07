<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\Template;

class Button implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;
    use EmitterHelper;

    public static function create(): Button
    {
        return new self(
            label: '',
            color: Style\Color::PRIMARY(),
            icon: null,
            size: Style\Size::MEDIUM(),
            enabled: true,
        );
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <button type="button" data-on-click="click" class="btn btn-{{ color }} {{ size }}" id="{{ id }}" {{ disabled }}>
                {{ icon }}{{ label }}
            </button>
            HTML,
            id: $this->id(),
            label: $this->label,
            color: $this->color,
            size: $this->size->prefixed('btn-'),
            icon: $this->icon?->html(),
            disabled: $this->enabled ? '' : 'disabled',
        );
    }

    public function click(): void
    {
        $this->emit($this->clicked());
    }

    public function clicked(): Signal
    {
        return Signal::auto();
    }

    public function set(
        ?string $label = null,
        ?Style\Color $color = null,
        Style\Icon|null|false $icon = false,
        ?Style\Size $size = null,
        ?bool $enabled = null,
    ): self {
        $this->label = $label ?? $this->label;
        $this->color = $color ?? $this->color;
        $this->icon = $icon === false ? $this->icon : $icon;
        $this->size = $size ?? $this->size;
        $this->enabled = $enabled ?? $this->enabled;
        $this->refresh();

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    private function __construct(
        private string $label,
        private Style\Color $color,
        private ?Style\Icon $icon,
        private Style\Size $size,
        private bool $enabled,
    ) {
    }
}
