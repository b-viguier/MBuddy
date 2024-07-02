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
            currentIndex: '',
            size: Style\Size::MEDIUM(),
        );
    }

    /**
     * @param array<int|string, string> $options
     */
    public function set(
        ?array $options = null,
        int|string|null $currentIndex = null,
        ?Style\Size $size = null,
    ): self {
        $this->options = $options ?? $this->options;
        $this->currentIndex = $currentIndex ?? $this->currentIndex;
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
                    selected: $index === $this->currentIndex ? 'selected' : '',
                ),
                $this->options,
                \array_keys($this->options),
            ),
        );
    }

    public function selectByText(string $option): void
    {
        $index = \array_search($option, $this->options, true);
        if ($index === false || $index === $this->currentIndex) {
            return;
        }

        $this->currentIndex = $index;
        $this->refresh();
        $this->emit($this->selected($option, $index));
    }

    public function selectByIndex(int|string $index): void
    {
        if ($index === $this->currentIndex || !isset($this->options[$index])) {
            return;
        }
        $this->currentIndex = $index;
        $this->refresh();
        $this->emit($this->selected($this->options[$index], $index));
    }

    public function selected(string $option, int|string $index): Signal
    {
        return Signal::auto();
    }

    /**
     * @param array<int|string, string> $options
     * @param int|string $currentIndex
     */
    private function __construct(
        private array $options,
        private mixed $currentIndex,
        private Style\Size $size,
    ) {
    }
}
