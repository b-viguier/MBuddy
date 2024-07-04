<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Select;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class Option implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;
    use EmitterHelper;

    public static function create(): self
    {
        return new self(
            value: '',
            text: '',
            selected: false,
        );
    }

    public function set(
        ?string $value = null,
        ?string $text = null,
        ?bool $selected = null,
    ): self {
        $this->value = $value ?? $this->value;
        $this->text = $text ?? $this->text;
        $this->selected = $selected ?? $this->selected;

        $this->refresh();

        return $this;
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <option id="{{ id }}" value="{{ value }}" {{ selected }}>{{ text }}</option>
            HTML,
            id: $this->id(),
            value: $this->value,
            selected: $this->selected ? 'selected' : '',
            text: $this->text,
        );
    }

    public function text(): string
    {
        return $this->text;
    }

    public function value(): string
    {
        return $this->value;
    }

    private function __construct(
        private string $value,
        private string $text,
        private bool $selected,
    ) {
    }
}
