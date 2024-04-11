<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Amp\Promise;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Core\Slot;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\Template;
use function Amp\asyncCall;
use Bveing\MBuddy\Motif\Master;

class NavBar implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;

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
            options: array_map(
                fn(Master\Id $id) => new Component\Option(sprintf("Preset %d", $id->toInt()), $id),
                iterator_to_array(Master\Id::all()),
            ),
            size: Style\Size::LARGE(),

        );

        $this->nextPreset = new Slot\Slot0(fn() => $this->nextPreset());
        $this->previousPreset = new Slot\Slot0(fn() => $this->previousPreset());
        $this->setPreset = new Slot\Slot1(fn(Preset $preset) => $this->setPreset($preset));

        $this->previousButton->clicked->connect($this->previousPreset);
        $this->nextButton->clicked->connect($this->nextPreset);
        $this->presetRepository->currentChanged->connect($this->setPreset);

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
            name: $this->currentPreset?->name() ?? 'none',
            preset_id: $this->currentPreset?->master()->id()->toInt() ?? 'none',
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

    private Slot\Slot0 $nextPreset;
    private Slot\Slot0 $previousPreset;
    /** @var Slot\Slot1<Preset> */
    private Slot\Slot1 $setPreset;


    private Component\Button $previousButton;
    private Component\Button $nextButton;

    /** @var Component\Select<Master\Id> $presetSelect */
    private Component\Select $presetSelect;

    private ?Preset $currentPreset = null;

    private function setPreset(Preset $preset): void
    {
        $this->currentPreset = $preset;
        $this->refresh();
    }
}
