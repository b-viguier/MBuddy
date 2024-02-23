<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use Amp\Loop;
use Bveing\MBuddy\Tests\GeckoServerExtension;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Component\Button;
use Bveing\MBuddy\Ui\SinglePageApp;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ButtonTest extends TestCase
{
    public function testCallbackWhenClicked(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp(
                "MBuddy",
                new NullLogger(),
                __DIR__ . '/../../../web/',
            );

            $counter1 = $counter2 = 0;

            $button1 = new Button(
                'B1',
                function() use (&$counter1) {
                    ++$counter1;
                },
            );

            $button2 = new Button(
                'B2',
                function() use (&$counter2) {
                    ++$counter2;
                },
            );

            $comp = new class ($button1, $button2) implements Component {
                use Component\Trait\NonModifiable;
                use Component\Trait\AutoId;

                public function __construct(private Button $button1, private Button $button2)
                {
                }

                public function render(): string
                {
                    return $this->button1->render() . $this->button2->render();
                }

                public function getChildren(): iterable
                {
                    yield $this->button1;
                    yield $this->button2;
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();

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
