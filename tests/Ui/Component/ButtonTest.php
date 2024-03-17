<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use Amp\Loop;
use Bveing\MBuddy\Core\Slot\Slot0;
use Bveing\MBuddy\Tests\GeckoServerExtension;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Component\Button;
use Bveing\MBuddy\Ui\SinglePageApp;
use Bveing\MBuddy\Ui\Template;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    public function testCallbackWhenClicked(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp();

            $counter1 = $counter2 = 0;
            $slot1 = new Slot0(function() use (&$counter1) {
                ++$counter1;
            });
            $slot2 = new Slot0(function() use (&$counter2) {
                ++$counter2;
            });

            $button1 = Button::create()->set(
                label: 'B1',
            );
            $button2 = Button::create()->set(
                label: 'B2',
            );

            $button1->clicked->connect($slot1);
            $button2->clicked->connect($slot2);

            $comp = new class ($button1, $button2) implements Component {
                use Component\Trait\AutoVersion;
                use Component\Trait\AutoId;

                public function __construct(private Button $button1, private Button $button2)
                {
                }

                public function template(): Template
                {
                    return Template::create(
                        "{{ A }} {{ B }}",
                        A: $this->button1,
                        B: $this->button2,
                    );
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();

                self::assertSame(0, $counter1);
                self::assertSame(0, $counter2);

                $elementId1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $button1->id()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $button2->id()));

                self::assertSame('B1', yield GeckoServerExtension::$driver->getElementText($elementId1));
                self::assertSame('B2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                yield GeckoServerExtension::$driver->clickElement($elementId1);

                self::assertSame(1, $counter1);
                self::assertSame(0, $counter2);

                yield GeckoServerExtension::$driver->clickElement($elementId2);

                self::assertSame(1, $counter1);
                self::assertSame(1, $counter2);


            } finally {
                yield $app->stop();
            }
        });
    }
}
