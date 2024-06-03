<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Amp\Promise;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\Template;
use function Amp\asyncCall;

class NavBar implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;
    use EmitterHelper;

    public function __construct(
        private Preset\Repository $presetRepository,
    ) {
        $this->previousButton = Component\Button::create()
            ->set(
                color: Style\Color::PRIMARY(),
                icon: Style\Icon::ARROW_LEFT_SQUARE_FILL(),
                size: Style\Size::LARGE(),
            );
        $this->nextButton = Component\Button::create()
            ->set(
                color: Style\Color::PRIMARY(),
                icon: Style\Icon::ARROW_RIGHT_SQUARE_FILL(),
                size: Style\Size::LARGE(),
            );

        $this->presetSelect = Component\Select::create()->set(
            options: \array_map(
                fn(Master\Id $id): Component\Option => new Component\Option(\sprintf("Preset %d", $id->toInt()), $id),
                \iterator_to_array(Master\Id::all()),
            ),
            size: Style\Size::LARGE(),
        );

        Siglot::connect0(
            \Closure::fromCallable([$this->previousButton, 'clicked']),
            \Closure::fromCallable([$this, 'previousPreset']),
        );
        Siglot::connect0(
            \Closure::fromCallable([$this->nextButton, 'clicked']),
            \Closure::fromCallable([$this, 'nextPreset']),
        );
        Siglot::connect1(
            \Closure::fromCallable([$this->presetRepository, 'currentChanged']),
            \Closure::fromCallable([$this, 'setPreset']),
        );
        Siglot::connect1(
            \Closure::fromCallable([$this->presetSelect, 'selected']),
            \Closure::fromCallable([$this, 'onSelectBoxChanged']),
        );


        asyncCall(function() {
            $this->setPreset(yield $this->presetRepository->current());
        });
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <nav id="{{ id }}" class="navbar navbar-light bg-dark">
                {{ previous }}
                <form class="form-inline w-75">
                    <div class="input-group w-100">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Preset</span>
                        </div>
                        {{ presetSelect }}
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary"><i class="bi bi-music-note-list"></i></button>
                        </div>
                    </div>
                </form>
                {{ next }}
            </nav>
            HTML,
            id: $this->id(),
            previous: $this->previousButton,
            next: $this->nextButton,
            presetSelect: $this->presetSelect,
        );
    }

    public function nextPreset(): void
    {
        Promise\rethrow($this->presetRepository->nextInBank());
    }


    public function previousPreset(): void
    {
        Promise\rethrow($this->presetRepository->previousInBank());
    }


    private Component\Button $previousButton;
    private Component\Button $nextButton;

    /** @var Component\Select<Master\Id> $presetSelect */
    private Component\Select $presetSelect;

    private function setPreset(Preset $preset): void
    {
        $entry = \sprintf("Preset %d", $preset->master()->id()->toInt());
        $this->presetSelect->select($entry);
    }

    /**
     * @param Component\Option<Master\Id> $option
     */
    private function onSelectBoxChanged(Component\Option $option): void
    {
        Promise\rethrow($this->presetRepository->setCurrent($option->value()));
    }
}
