<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use PHPUnit\Framework\TestCase;
use Amp\Loop;
use Bveing\MBuddy\Ui\SinglePageApp;
use Psr\Log\NullLogger;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Tests\GeckoServerExtension;

use function Amp\delay;

class LabelTest extends TestCase
{
    public function testTextChange(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp(
                "MBuddy",
                new NullLogger(),
                __DIR__ . '/../../../web/',
            );

            $label1 = new Component\Label(
                'L1',
            );

            $label2 = new Component\Label(
                'L2',
            );

            $comp = new class ($label1, $label2) implements Component {
                use Component\Trait\NonModifiable;
                use Component\Trait\AutoId;
                public function __construct(private Component\Label $label1, private Component\Label $label2)
                {
                }

                public function render(): string
                {
                    return $this->label1->render() . $this->label2->render();
                }

                public function getChildren(): iterable
                {
                    yield $this->label1;
                    yield $this->label2;
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();

                $elementId1 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label1->getId()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label2->getId()));

                $this->assertSame('L1', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('L2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                $label1->setText('Hello');
                yield $app->refresh();

                $elementId1 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label1->getId()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label2->getId()));

                $this->assertSame('Hello', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('L2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                $label2->setText('World');
                yield $app->refresh();

                $elementId1 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label1->getId()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(sprintf('#%s', $label2->getId()));

                $this->assertSame('Hello', yield GeckoServerExtension::$driver->getElementText($elementId1));
                $this->assertSame('World', yield GeckoServerExtension::$driver->getElementText($elementId2));


            } finally {
                yield $app->stop();
            }
        });
    }
}
