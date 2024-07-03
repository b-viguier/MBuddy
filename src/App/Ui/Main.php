<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;
use function Amp\call;
use function Amp\Promise\rethrow;

class Main implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\Refreshable;
    use EmitterHelper;

    public function __construct(
        Preset\Repository $presetRepository
    ) {
        $this->navBar = new NavBar($presetRepository);
        $this->motifView = new MotifView();

        Siglot::connect1(
            \Closure::fromCallable([$presetRepository, 'currentChanged']),
            \Closure::fromCallable([$this->motifView, 'setPreset']),
        );

        rethrow(call(function() use ($presetRepository) {
            $this->motifView->setPreset(yield $presetRepository->current());
        }));
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <div class="container-fluid h-100 px-0 d-flex flex-column">
                <!-- NavBar -->
                {{ navbar }}

                <div class="row align-items-center no-gutters flex-grow-1">

                <!-- Left panel -->
                <div class="col-8 pl-3 pr-1">
                    {{ motifView }}
                
                    
                    <div class="mt-4"></div> <!-- Spacer -->
                    
                </div>
                
                <!-- Right panel -->
                <div class="col pr-3 pl-1">
                    
                    <div class="mt-4"></div> <!-- Spacer -->
                    
                </div>
                
            </div>
            HTML,
            navbar: $this->navBar,
            motifView: $this->motifView,
        );
    }
    private NavBar $navBar;
    private MotifView $motifView;
}
