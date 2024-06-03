<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Bveing\MBuddy\Siglot;
use Bveing\MBuddy\Ui;

Amp\Loop::run(function() {
    $logger = new \Bveing\MBuddy\Infrastructure\ConsoleLogger();

    $app = new Ui\SinglePageApp(
        "MBuddy",
        $logger,
        __DIR__ . '/',
    );


    $body = new class () implements Ui\Component {
        use Ui\Component\Trait\Refreshable;
        use Ui\Component\Trait\AutoId;
        use Siglot\EmitterHelper;

        public function __construct()
        {
            $this->selectBox = Ui\Component\SelectBox::create()->set(
                "Select",
                ["Option 1", "Option 2", "Option 3"],
                "Option 1",
            );
            Siglot\Siglot::connect1(
                \Closure::fromCallable([$this->selectBox, 'selected']),
                \Closure::fromCallable([$this, 'onSelected']),
            );

            $this->button1 = Ui\Component\Button::create()->set(
                label: "Open",
                color: Ui\Style\Color::SECONDARY(),
                icon: Ui\Style\Icon::ARROW_DOWN(),
            );
            Siglot\Siglot::connect1(
                \Closure::fromCallable([$this->button1, 'clicked']),
                \Closure::fromCallable([$this->selectBox, 'show']),
            );

            $this->label = new Ui\Component\Label("Label");
        }

        public function template(): Ui\Template
        {
            return Ui\Template::create(
                <<<HTML
                <div>
                    <h1>Hello World</h1>
                    {{ button1 }}
                    {{ label }}
                    {{ selectBox }}
                </div>
                HTML,
                button1: $this->button1,
                label: $this->label,
                selectBox: $this->selectBox,
            );
        }

        private function onSelected(string $selected): void
        {
            $this->label->setText($selected);
        }

        private Ui\Component\Button $button1;

        private Ui\Component\Label $label;
        private Ui\Component\SelectBox $selectBox;


    };

    $masterRepository = new Bveing\MBuddy\Motif\Master\Repository\Decorator\Delay(
        100,
        new \Bveing\MBuddy\Motif\Master\Repository\InMemory(),
    );
    $presetRepository = new \Bveing\MBuddy\App\Core\Preset\Repository($masterRepository);
    $main = new \Bveing\MBuddy\App\Ui\Main($presetRepository);

    yield $app->start($main);
});

echo "Server stopped\n";
