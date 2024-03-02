<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Motif;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;

class Main implements Component
{
    public function __construct(
        private JsEventBus $jsEventBus,
    ) {
        $this->navBar = new NavBar($this->jsEventBus, new Motif\Preset());

        $this->motifLayout = new KeyboardLayout(
            $this->jsEventBus,
            'Motif',
            new Motif\Part(Motif\Channel::fromMidiByte(0), 'Piano', true),
            new Motif\Part(Motif\Channel::fromMidiByte(1), 'Piano', false),
            new Motif\Part(Motif\Channel::fromMidiByte(2), 'Piano', false),
            new Motif\Part(Motif\Channel::fromMidiByte(3), 'Piano', false),
        );

        $this->impulseLayout = new KeyboardLayout(
            $this->jsEventBus,
            'Impulse',
            new Motif\Part(Motif\Channel::fromMidiByte(7), 'Piano', true),
            new Motif\Part(Motif\Channel::fromMidiByte(8), 'Piano', false),
            new Motif\Part(Motif\Channel::fromMidiByte(9), 'Piano', false),
            new Motif\Part(Motif\Channel::fromMidiByte(10), 'Piano', false),
        );

        $this->scoreViewer = new ScoreViewer(
            $this->jsEventBus,
            'scores/01-IWish.png',
        );

        $this->buttonGroup = new ButtonGroup(
            $this->jsEventBus,
        );
    }

    public function render(): string
    {
        return <<<HTML
            <div class="container-fluid h-100 px-0 d-flex flex-column">
                <!-- NavBar -->
                {$this->navBar->render()}

                <div class="row align-items-center no-gutters flex-grow-1">

                <!-- Left panel -->
                <div class="col-8 pl-3 pr-1">
                    {$this->motifLayout->render()}
                    <div class="mt-4"></div> <!-- Spacer -->
                    {$this->impulseLayout->render()}
                </div>
                
                <!-- Right panel -->
                <div class="col pr-3 pl-1">
                    {$this->scoreViewer->render()}
                    <div class="mt-4"></div> <!-- Spacer -->
                    {$this->buttonGroup->render()}
                </div>
                
            </div>
            HTML;
    }
    private NavBar $navBar;
    private KeyboardLayout $motifLayout;
    private KeyboardLayout $impulseLayout;

    private ScoreViewer $scoreViewer;
    private ButtonGroup $buttonGroup;
}
