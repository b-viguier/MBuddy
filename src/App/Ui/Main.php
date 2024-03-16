<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class Main implements Component
{
    use Component\Trait\AutoId;
    use Component\Trait\AutoVersion;

    public function __construct(
    ) {
        $this->navBar = new NavBar();
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
                    
                    <div class="mt-4"></div> <!-- Spacer -->
                    
                </div>
                
                <!-- Right panel -->
                <div class="col pr-3 pl-1">
                    
                    <div class="mt-4"></div> <!-- Spacer -->
                    
                </div>
                
            </div>
            HTML,
            navbar: $this->navBar
        );
    }
    private NavBar $navBar;
}
