<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;

class Modal implements Component
{
    use Trait\AutoId;
    use Trait\NonModifiable;

    public function __construct(
        private Component $content,
        private ?Component $header = null,
        private ?Component $footer = null,
    ) {
        $this->script = new Script();
    }

    public function show(): void
    {
        $this->visible = true;
        $this->modalExec('show');
    }

    public function hide(): void
    {
        $this->visible = false;
        $this->modalExec('hide');
    }

    public function toggle(): void
    {
        $this->visible = !$this->visible;
        $this->modalExec('toggle');
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function render(): string
    {
        $header = "";
        if ($this->header) {
            $header = <<<HTML
                <div class="modal-header">
                    {$this->header->render()}
                </div>
                HTML;
        }

        $footer = "";
        if ($this->footer) {
            $footer = <<<HTML
                <div class="modal-footer">
                    {$this->footer->render()}
                </div>
                HTML;
        }

        return <<<HTML
            <div class="modal fade" tabindex="-1" id="{$this->id()}" data-backdrop="static" data-keyboard="false">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  {$header}
                  <div class="modal-body">
                    {$this->content->render()}
                  </div>
                  {$footer}
                </div>
              </div>
              {$this->script->render()}
            </div>
            HTML;
    }

    public function children(): iterable
    {
        yield $this->script;
        yield $this->content;
        $this->header === null ?: yield $this->header;
        $this->footer === null ?: yield $this->footer;
    }

    private function modalExec(string $action): void
    {
        $this->script->exec("$('#{$this->id()}').modal('{$action}')");
    }

    private bool $visible = false;
    private Script $script;
}
