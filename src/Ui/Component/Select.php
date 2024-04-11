<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\Template;
use Bveing\MBuddy\Ui\SubTemplate;

/**
 * @template T
 */
class Select implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;

    /** @var Signal\Signal1<Option<T>> */
    public Signal\Signal1 $selected;

    /**
     * @return self<T>
     */
    public static function create(): self
    {
        /** @var self<T> */
        $instance = new self(
            options: [],
            currentValue: null,
            size: Style\Size::MEDIUM(),
        );

        return $instance;
    }

    /**
     * @param list<Option<T>> $options
     * @param T|null $currentValue
     * @return self<T>
     */
    public function set(
        ?array $options = null,
        mixed $currentValue = null,
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
            <select class="custom-select {{ size }}" id="{{ id }}" data-on-change="select">
                {{ options }}
            </select>
            HTML,
            id: $this->id(),
            size: $this->size->prefixed('custom-select-'),
            options: \array_map(
                fn(Option $option, int $index) => SubTemplate::create(
                    <<<HTML
                    <option value="{{ index }}" {{ selected }}>{{ text }}</option>
                    HTML,
                    index: $index,
                    text: $option->text(),
                    selected: $option->value() === $this->currentValue ? 'selected' : '',
                ),
                $this->options,
                \array_keys($this->options),
            ),
        );
    }

    public function select(string $strIndex): void
    {
        $index = (int) $strIndex;
        $option = $this->options[$index] ?? null;

        if ($option === null) {
            return;
        }

        $this->currentValue = $option->value();
        $this->selected->emit($option);
    }

    /**
     * @param list<Option<T>> $options
     * @param T $currentValue
     */
    private function __construct(
        private array $options,
        private mixed $currentValue,
        private Style\Size $size,
    ) {
        $this->selected = new Signal\Signal1();
    }
}

/**
 * @template T
 */
class Option
{
    /**
     * @param T $value
     */
    public function __construct(
        private string $text,
        private mixed $value,
    ) {
    }

    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return T
     */
    public function value(): mixed
    {
        return $this->value;
    }
}
