<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Motif;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;

class Part implements Component
{
    public function __construct(
        private JsEventBus $jsEventBus,
        private Motif\Part $part,
    ) {
    }

    public function render(): string
    {
        return <<<HTML
            <div class="row">
                <form class="form-inline w-100 mb-1">
                    <div class="input-group w-100">
                        <div class="input-group-prepend">
                            <div class="input-group-text justify-content-center border-dark bg-{$this->getColor()}" style="width:55px">
                                <span class="badge badge-dark">{$this->part->getChannel()}</span>
                            </div>
                        </div>
                        <input type="text" class="form-control user-select-none border-dark" value="{$this->part->getVoiceName()}" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary border-dark p-0" style="width:55px"><span class="badge badge-dark">C#-1</span></button>
                            <button type="button" class="btn btn-secondary border-dark p-0" style="width:55px"><span class="badge badge-dark">F#6</span></button>
                            <button type="button" class="btn btn-secondary border-dark p-0" style="width:55px"><span class="badge badge-dark">+16</span></button>
                        </div>
                    </div>
                </form>
            </div>
            HTML;
    }

    private function getColor(): string
    {
        return $this->part->isEnabled() ? 'success' : 'danger';
    }
}
