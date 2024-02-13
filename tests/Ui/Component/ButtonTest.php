<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Ui\Component\Button;
use Bveing\MBuddy\Ui\SinglePageApp;
use Psr\Log\NullLogger;
use Bveing\MBuddy\Tests\GeckoServerExtension;
use Amp\Loop;
use Bveing\MBuddy\Ui\Component;

class ButtonTest extends TestCase
{
    public function testCallbackWhenClicked(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp(
                "MBuddy",
                new NullLogger(),
            );

            $counter1 = $counter2 = 0;

            $button1 = new Button(
                'B1',
                function() use (&$counter1) {
                    ++$counter1;
                },
                $app->getJsEventBus(),
            );

            $button2 = new Button(
                'B2',
                function() use (&$counter2) {
                    ++$counter2;
                },
                $app->getJsEventBus(),
            );

            $comp = new class ($button1, $button2) implements Component {
                public function __construct(private Button $button1, private Button $button2)
                {
                }

                public function render(): string
                {
                    return $this->button1->render() . $this->button2->render();
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();
                file_put_contents(__DIR__ . '/screenshot.png', yield GeckoServerExtension::$driver->takeScreenshot());

                $this->assertSame(0, $counter1);
                $this->assertSame(0, $counter2);

                $elementId1 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $button1->getId()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $button2->getId()));

                $this->assertSame('B1', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('B2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                yield GeckoServerExtension::$driver->clickElement($elementId1);
                $this->assertSame(1, $counter1);
                $this->assertSame(0, $counter2);

                yield GeckoServerExtension::$driver->clickElement($elementId2);
                $this->assertSame(1, $counter1);
                $this->assertSame(1, $counter2);


            } finally {
                yield $app->stop();
            }
        });
    }
}
