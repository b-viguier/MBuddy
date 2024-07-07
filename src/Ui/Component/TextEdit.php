<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class TextEdit implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;
    use EmitterHelper;

    public static function create(): self
    {
        return new self(
            value: '',
            maxLength: 255,
        );
    }

    /**
     * @param ?positive-int $maxLength
     * @return $this
     */
    public function set(
        ?string $value = null,
        ?int $maxLength = null,
    ): self {
        $this->value = $value ?? $this->value;
        $this->maxLength = $maxLength ?? $this->maxLength;
        $this->refresh();

        return $this;
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <input 
                type="text"
                class="form-control"
                id="{{ id }}"
                data-on-change="setText"
                value="{{ value }}"
                maxlength="{{ maxLength }}"
            >
            HTML,
            id: $this->id(),
            value: $this->value,
            maxLength: $this->maxLength,
        );
    }

    public function setText(string $value): void
    {
        $this->value = \substr($value, 0, $this->maxLength);
        $this->refresh();
    }

    public function text(): string
    {
        return $this->value;
    }

    /**
     * @param positive-int $maxLength
     */
    private function __construct(
        private string $value,
        private int $maxLength,
    ) {
    }
}
