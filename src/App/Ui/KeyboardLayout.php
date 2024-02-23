<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Motif;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;

class KeyboardLayout implements Component
{
    private array $parts;

    public function __construct(
        private JsEventBus $jsEventBus,
        private string $name,
        Motif\Part ...$parts
    ) {
        $this->parts = array_map(fn($part) => new Part($this->jsEventBus, $part), $parts);
    }

    public function render(): string
    {
        return <<<HTML
            <div class="card bg-secondary">
                <div class="card-header bg-secondary p-1 pl-3">
                    <h4 class="m-0">{$this->name}</h4>
                </div>

                <div class="card-body bg-dark">
                    {$this->renderParts()}
                </div>
            </div>
            HTML;
    }

    private function renderParts(): string
    {
        return implode(PHP_EOL, array_map(fn($part) => $part->render(), $this->parts));
    }
}
