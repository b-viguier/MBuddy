<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
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
     * @param array<string, string> $options
     */
    public function set(
        ?array $options = null,
        ?string $currentIndex = null,
        ?Style\Size $size = null,
    ): self {
        $this->currentIndex = $currentIndex ?? $this->currentIndex;
        $this->size = $size ?? $this->size;

        $this->options = $options === null ? $this->options : self::createOptionsFromArray($options, $this->currentIndex);

        $this->refresh();

        return $this;
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <select autocomplete="off" class="custom-select {{ size }}" id="{{ id }}" data-on-change="selectByIndex">
                {{ options }}
            </select>
            HTML,
            id: $this->id(),
            size: $this->size->prefixed('custom-select-'),
            options: $this->options,
        );
    }

    public function selectByIndex(string $index): void
    {
        if ($index === $this->currentIndex || !isset($this->options[$index])) {
            return;
        }
        if(isset($this->options[$this->currentIndex])) {
            $this->options[$this->currentIndex]->set(selected: false);
        }
        $this->currentIndex = $index;
        $this->options[$index]->set(selected: true);
        $this->emit($this->selected($this->options[$index]->text(), $index));
    }

    public function selected(string $option, string $index): Signal
    {
        return Signal::auto();
    }

    /**
     * @param array<string, string> $options
     */
    private function __construct(
        array $options,
        private string $currentIndex,
        private Style\Size $size,
    ) {
        $this->options = self::createOptionsFromArray($options, $currentIndex);
    }

    /**
     * @var array<string,Select\Option> $options
     */
    private array $options;

    /**
     * @param array<string,string> $options
     * @return array<string,Select\Option>
     */
    private static function createOptionsFromArray(array $options, string $currentIndex): array
    {
        $values = \array_keys($options);
        return \array_combine(
            $values,
            \array_map(
                fn(string $text, string $value) => Select\Option::create()->set(
                    value: $value,
                    text: $text,
                    selected: $value === $currentIndex,
                ),
                $options,
                $values,
            ),
        );
    }
}
