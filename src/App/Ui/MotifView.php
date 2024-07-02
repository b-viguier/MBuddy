<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class MotifView implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;
    use EmitterHelper;

    public function __construct()
    {
        $this->currentPreset = Preset::default();
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
            name: $this->currentPreset->name(),
            presetId: $this->currentPreset->master()->id()->toInt(),
        );
    }

    public function setPreset(Preset $preset): void
    {
        //TODO: switch to "in memory" preset
        $this->currentPreset = $preset;
        $this->refresh();
    }

    private Preset $currentPreset;
}
