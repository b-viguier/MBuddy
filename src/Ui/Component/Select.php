<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\SubTemplate;
use Bveing\MBuddy\Ui\Template;

class Select implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;
    use EmitterHelper;

    public static function create(): self
    {
        return new self(
            options: [],
            currentValue: '',
            size: Style\Size::MEDIUM(),
        );
    }

    /**
     * @param array<int|string, string> $options
     */
    public function set(
        ?array $options = null,
        ?string $currentValue = null,
        ?Style\Size $size = null,
    ): self {
        $this->options = $options ?? $this->options;
        $this->currentValue = $currentValue ?? $this->currentValue;
        $this->size = $size ?? $this->size;

        $this->refresh();

        return $this;
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <select class="custom-select {{ size }}" id="{{ id }}" data-on-change="selectByIndex">
                {{ options }}
            </select>
            HTML,
            id: $this->id(),
            size: $this->size->prefixed('custom-select-'),
            options: \array_map(
                fn(string $option, int|string $index) => SubTemplate::create(
                    <<<HTML
                    <option value="{{ index }}" {{ selected }}>{{ text }}</option>
                    HTML,
                    index: $index,
                    text: $option,
                    selected: $option === $this->currentValue ? 'selected' : '',
                ),
                $this->options,
                \array_keys($this->options),
            ),
        );
    }

    public function selectByText(string $option): void
    {
        $index = \array_search($option, $this->options, true);
        if ($index === false || $option === $this->currentValue) {
            return;
        }

        $this->currentValue = $option;
        $this->refresh();
        $this->emit($this->selected($option, $index));
    }

    public function selectByIndex(int|string $index): void
    {
        $option = $this->options[$index] ?? $this->currentValue;
        if ($option === $this->currentValue) {
            return;
        }

        $this->currentValue = $option;
        $this->refresh();
        $this->emit($this->selected($option, $index));
    }

    public function selected(string $option, int|string $index): Signal
    {
        return Signal::auto();
    }

    /**
     * @param array<int|string, string> $options
     * @param string $currentValue
     */
    private function __construct(
        private array $options,
        private mixed $currentValue,
        private Style\Size $size,
    ) {
    }
}
