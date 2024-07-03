<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Amp\Promise;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Style;
use Bveing\MBuddy\Ui\Template;
use function Amp\call;

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
        Siglot::connect2(
            \Closure::fromCallable([$this->presetSelect, 'selected']),
            \Closure::fromCallable([$this, 'onSelectBoxChanged']),
        );


        $this->loadAllPresets();
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <nav id="{{ id }}" class="navbar navbar-light bg-dark">
                {{ previous }}
                {{ loading }}
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
            loading: $this->isLoading ? '<div class="spinner-border text-primary"></div>' : '',
        );
    }

    public function nextPreset(): void
    {
        $nextId = $this->currentPresetId->next();
        if ($nextId === null) {
            return;
        }
        Promise\rethrow($this->presetRepository->setCurrentId($nextId));
    }


    public function previousPreset(): void
    {
        $previousId = $this->currentPresetId->previous();
        if ($previousId === null) {
            return;
        }
        Promise\rethrow($this->presetRepository->setCurrentId($previousId));
    }


    private Component\Button $previousButton;
    private Component\Button $nextButton;

    private Component\Select $presetSelect;

    private Preset\Id $currentPresetId;

    private bool $isLoading = false;

    private function setPreset(Preset $preset): void
    {
        $this->currentPresetId = $preset->id();
        $this->presetSelect->selectByIndex((string) $preset->id()->toInt());
    }

    private function onSelectBoxChanged(string $option, int|string $index): void
    {
        Promise\rethrow($this->presetRepository->setCurrentId(
            Preset\Id::fromInt((int) $index)
        ));
    }

    private function loadAllPresets(): void
    {
        $this->isLoading = true;
        $this->refresh();

        Promise\rethrow(call(function() {
            $selectOptions = [];
            foreach (Preset\Id::all() as $id) {
                /** @var Preset $preset */
                $preset = yield $this->presetRepository->load($id);
                /** @var string $key */
                $key = (string)$id->toInt();
                $selectOptions[$key] = \sprintf('[%03d] %s', $id->toInt(), $preset->master()->name());
            }
            $this->currentPresetId = yield $this->presetRepository->currentId();


            $this->presetSelect->set(
                options: $selectOptions,
                currentIndex: (string) $this->currentPresetId->toInt(),
            );


            $this->isLoading = false;
            $this->refresh();
        }));
    }
}
