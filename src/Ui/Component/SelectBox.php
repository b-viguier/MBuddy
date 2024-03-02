<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;

class SelectBox implements Component
{
    use Trait\AutoId;
    use Trait\NonModifiable;

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

    public function render(): string
    {
        return $this->modal->render();
    }

    public function children(): iterable
    {
        yield $this->modal;
        // Html is owned by the Modal
    }

    private Modal $modal;
    private Html $html;

    public function onSelected(string $index): void
    {
        $this->selected = $this->options[(int)$index];
        $this->refresh();
    }

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
