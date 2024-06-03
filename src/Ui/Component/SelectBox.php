<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Siglot\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style\Icon;
use Bveing\MBuddy\Ui\Template;

class SelectBox implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;
    use EmitterHelper;

    public static function create(): SelectBox
    {
        return new self(
            label: '',
            options: [],
            currentText: null,
        );
    }

    /**
     * @param array<string> $options
     */
    public function set(
        string $label = null,
        array $options = null,
        string|null|false $currentText = false,
    ): self {
        $this->label = $label ?? $this->label;
        $this->options = $options ?? $this->options;
        $this->currentText = $currentText === false ? $this->currentText : $currentText;

        return $this->refresh();
    }

    public function show(): void
    {
        $this->modal->show();
    }

    public function hide(): void
    {
        $this->modal->hide();
    }

    public function template(): Template
    {
        return Template::create('{{ modal }}', modal: $this->modal);
    }

    public function select(string $index): void
    {
        $this->currentText = $this->options[(int)$index];
        $this->emit($this->selected($this->currentText));
        $this->refresh();
    }

    public function selected(string $text): Signal
    {
        return Signal::auto();
    }

    /**
     * @param array<string> $options
     */
    private function __construct(
        private string $label,
        private array $options,
        private ?string $currentText,
    ) {
        $closeBtn = Button::create()->set(
            label: 'Close',
            icon: Icon::X_CIRCLE_FILL(),
        );
        Siglot::connect0(
            \Closure::fromCallable([$closeBtn, 'clicked']),
            \Closure::fromCallable([$this, 'hide']),
        );

        $this->modal = new Modal(
            $this->html = new Html(''),
            header: new Html("<h5>{$this->label}</h5>"),
            footer: $closeBtn,
        );
        $this->refresh();
    }

    private Modal $modal;
    private Html $html;

    private function refresh(): self
    {
        $list = \join(
            '',
            \array_map(
                function(string $option, int $index) {
                    $active = $option === $this->currentText ? 'active' : '';
                    return <<<HTML
                        <a class="list-group-item list-group-item-action $active" data-on-click="select" data-target-id="{$this->id()}" data-value="$index">
                            $option
                        </a>
                        HTML;
                },
                $this->options,
                \array_keys($this->options),
            ),
        );
        $this->html->set(
            <<<HTML
            <ul class="list-group">
                $list
            </ul>
            HTML
        );

        return $this;
    }
}
