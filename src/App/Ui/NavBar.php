<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\Template;

class NavBar implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\AutoVersion;

    public function __construct(
    ) {
        $this->previous = Component\Button::create()
            ->set(
                color: Style\Color::PRIMARY(),
                icon: Style\Icon::ARROW_LEFT_SQUARE_FILL(),
            );
        $this->next = Component\Button::create()
            ->set(
                color: Style\Color::PRIMARY(),
                icon: Style\Icon::ARROW_RIGHT_SQUARE_FILL(),
            );
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <nav class="navbar navbar-light bg-dark">
                {{ previous }}
                <form class="form-inline w-75">
                    <div class="input-group w-100">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary" type="button">Preset</button>
                        </div>
                        <input type="text" class="form-control user-select-none" placeholder="TODO" readonly>

                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary"><i class="bi bi-music-note-list"></i></button>
                        </div>
                    </div>
                </form>
                {{ next }}
            </nav>
            HTML,
            previous: $this->previous,
            next: $this->next,
        );
    }

    private Component\Button $previous;
    private Component\Button $next;
}
