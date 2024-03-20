<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\SubTemplate;
use Bveing\MBuddy\Ui\Template;

class Modal implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;

    public function __construct(
        private Component $content,
        ?Component $header = null,
        ?Component $footer = null,
    ) {
        $this->script = new Script();
        $this->header = $header !== null
            ? SubTemplate::create(
                <<<HTML
                <div class="modal-header">
                    {{ header }}
                </div>
                HTML,
                header: $header,
            ) : null;
        $this->footer = $footer !== null
            ? SubTemplate::create(
                <<<HTML
                <div class="modal-footer">
                    {{ footer }}
                </div>
                HTML,
                footer: $footer,
            ) : null;
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

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <div class="modal fade" tabindex="-1" id="{{ id }}" data-backdrop="static" data-keyboard="false">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  {{ header }}
                  <div class="modal-body">
                    {{ content }}
                  </div>
                  {{ footer }}
                </div>
              </div>
              {{ script }}
            </div>
            HTML,
            id: $this->id(),
            header: $this->header,
            content: $this->content,
            footer: $this->footer,
            script: $this->script,
        );
    }

    private function modalExec(string $action): void
    {
        $this->script->exec("$('#{$this->id()}').modal('{$action}')");
    }

    private bool $visible = false;
    private Script $script;
    private ?SubTemplate $header;
    private ?SubTemplate $footer;
}
