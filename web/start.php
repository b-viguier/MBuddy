<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Bveing\MBuddy\Ui;

Amp\Loop::run(function() {
    $logger = new \Bveing\MBuddy\Infrastructure\ConsoleLogger();

    $app = new Ui\SinglePageApp(
        "MBuddy",
        $logger,
        __DIR__ . '/',
    );


    $body = new class () implements Ui\Component {
        use Ui\Component\Trait\AutoVersion;
        use Ui\Component\Trait\AutoId;

        public function __construct()
        {
            $this->selectBox = new Ui\Component\SelectBox(
                "Select",
                ["Option 1", "Option 2", "Option 3"],
                "Option 1",
                fn($selected) => $this->label->setText("Selected: $selected"),
            );
            $this->button1 = (new Ui\Component\Button(
                "Open",
                fn() => $this->selectBox->show(),
            ))->set(color: Ui\Style\Color::SECONDARY());
            $this->label = new Ui\Component\Label("Label");
        }

        public function template(): Ui\Rendering\Template
        {
            return Ui\Rendering\Template::create(
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

        private Ui\Component\Button $button1;

        private Ui\Component\Label $label;
        private Ui\Component\SelectBox $selectBox;


    };

    yield $app->start($body);
});

echo "Server stopped\n";
