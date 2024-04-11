<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Core\Slot\Slot1;

class MotifView implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;

    public function __construct(
        private Preset\Repository $presetRepository
    ) {
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <div>
                {{ name }}
            </div>
            HTML,
            navbar: $this->navBar,
            name: $this->presetRepository->current()->name(),
        );
    }

    public Slot1 $onCurrentChanged;
    public function onCurrentChanged(Preset $preset): void
    {
        $this->refresh();
    }
}
