<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class SelectBox implements Component
{
    use Trait\AutoId;

    /**
     * @param list<string> $options
     * @param \Closure(string):void $onSelected
     */
    public function __construct(
        private string $label,
        private array $options,
        private string $selected,
        private \Closure $onSelected,
    ) {

        $this->modal = new Modal(
            $this->html = new Html(''),
            header: new Html("<h5>{$this->label}</h5>"),
            footer: new Button('Close', function() {
                $this->modal->hide();
                ($this->onSelected)($this->selected);
            }),
        );
        $this->refresh();
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
        return $this->modal->template();
    }

    public function version(): int
    {
        return $this->modal->version();
    }

    public function onSelected(string $index): void
    {
        $this->selected = $this->options[(int)$index];
        $this->refresh();
    }

    private Modal $modal;
    private Html $html;

    private function refresh(): void
    {
        $list = \join(
            '',
            \array_map(
                function(string $option, int $index) {
                    $active = $option === $this->selected ? 'active' : '';
                    return <<<HTML
                        <a class="list-group-item list-group-item-action $active" data-on-click="onSelected" data-target-id="{$this->id()}" data-value="$index">
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
    }
}
