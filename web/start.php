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
            $this->slotOnSelected = new \Bveing\MBuddy\Core\Slot\Slot1(function(string $selected) {
                $this->label->setText($selected);
            });

            $this->selectBox = Ui\Component\SelectBox::create()->set(
                "Select",
                ["Option 1", "Option 2", "Option 3"],
                "Option 1",
            );
            $this->selectBox->signalOnSelected->connect($this->slotOnSelected);

            $this->button1 = Ui\Component\Button::create()->set(
                label: "Open",
                color: Ui\Style\Color::SECONDARY(),
                icon: Ui\Style\Icon::ARROW_DOWN(),
            );
            $this->button1->signalOnClick->connect($this->selectBox->slotShow);
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

        /**
         * @var \Bveing\MBuddy\Core\Slot\Slot1<string> $slotOnSelected
         */
        private \Bveing\MBuddy\Core\Slot\Slot1 $slotOnSelected;

        private Ui\Component\Button $button1;

        private Ui\Component\Label $label;
        private Ui\Component\SelectBox $selectBox;


    };

    yield $app->start($body);
});

echo "Server stopped\n";
