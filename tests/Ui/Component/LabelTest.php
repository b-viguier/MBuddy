<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use PHPUnit\Framework\TestCase;
use Amp\Loop;
use Bveing\MBuddy\Ui\SinglePageApp;
use Psr\Log\NullLogger;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Tests\GeckoServerExtension;

class LabelTest extends TestCase
{
    public function testTextChange(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp(
                "MBuddy",
                new NullLogger(),
            );

            $label1 = new Component\Label(
                'L1',
                $app->getJsEventBus(),
            );

            $label2 = new Component\Label(
                'L2',
                $app->getJsEventBus(),
            );

            $comp = new class ($label1, $label2) implements Component {
                public function __construct(private Component\Label $label1, private Component\Label $label2)
                {
                }

                public function render(): string
                {
                    return $this->label1->render() . $this->label2->render();
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();
                file_put_contents(__DIR__ . '/screenshot.png', yield GeckoServerExtension::$driver->takeScreenshot());

                $elementId1 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label1->getId()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label2->getId()));

                $this->assertSame('L1', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('L2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                $label1->setLabel('Hello');
                $this->assertSame('Hello', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('L2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                $label2->setLabel('World');
                $this->assertSame('Hello', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('World', yield GeckoServerExtension::$driver->getElementText($elementId2));


            } finally {
                yield $app->stop();
            }
        });
    }
}
