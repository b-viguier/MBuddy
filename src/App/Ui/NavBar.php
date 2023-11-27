<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;
use Bveing\MBuddy\Motif\Preset;

class NavBar implements Component
{
    public function __construct(
        private JsEventBus $jsEventBus,
        private Preset $preset,
    ) {
    }

    public function render(): string
    {
        return <<<HTML
            <nav class="navbar navbar-light bg-dark">
                <button type="button" class="btn btn-primary"><i class="bi bi-arrow-left-square-fill"></i></button>
                <form class="form-inline w-75">
                    <div class="input-group w-100">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary" type="button">Preset</button>
                        </div>
                        <input type="text" class="form-control user-select-none" placeholder="{$this->preset->getMasterName()}" readonly>
                        <input type="text" class="form-control user-select-none" placeholder="{$this->preset->getSongName()}" readonly>

                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary"><i class="bi bi-music-note-list"></i></button>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn btn-primary"><i class="bi bi-arrow-right-square-fill"></i></button>
            </nav>
            HTML;

    }
}
