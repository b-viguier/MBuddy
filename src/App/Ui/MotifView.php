<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class MotifView implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;
    use EmitterHelper;

    public function __construct(
        Preset\Repository $presetRepository
    ) {
        Siglot::connect1(
            \Closure::fromCallable([$presetRepository, 'currentChanged']),
            \Closure::fromCallable([$this, 'onCurrentChanged']),
        );
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <div id="{{ id }}">
                Current: {{ name }} ({{ presetId }})
            </div>
            HTML,
            id: $this->id(),
            name: $this->currentPreset?->name(),
            presetId: $this->currentPreset?->master()->id()->toInt(),
        );
    }

    private function onCurrentChanged(Preset $preset): void
    {
        $this->currentPreset = $preset;
        $this->refresh();
    }

    private ?Preset $currentPreset = null;
}
