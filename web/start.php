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
        use Ui\Component\Trait\NonModifiable;
        use Ui\Component\Trait\AutoId;

        private Ui\Component\Button $button1;
        private Ui\Component\Button $button2;

        private Ui\Component\Label $label;

        public function __construct()
        {
            $this->button1 = (new Ui\Component\Button(
                "Button 1",
                fn() => $this->label->setText("Button 1 clicked"),
            ))->set(color: Ui\Style\Color::SECONDARY());
            $this->button2 = new Ui\Component\Button(
                "Button 2",
                function() {
                    $this->label->setText("Button 2 clicked");
                    $this->button1->set(label: "Oops!");
                },
            );
            $this->label = new Ui\Component\Label("Click a button");
        }

        public function render(): string
        {
            return <<<HTML
                <div>
                    <h1>Hello World</h1>
                    {$this->button1->render()}
                    {$this->label->render()}
                    {$this->button2->render()}
                </div>
                HTML;
        }

        public function children(): iterable
        {
            yield $this->button1;
            yield $this->button2;
            yield $this->label;
        }


    };

    yield $app->start($body);
});

echo "Server stopped\n";
