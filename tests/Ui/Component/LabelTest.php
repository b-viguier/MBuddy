<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use Amp\Loop;
use Bveing\MBuddy\Tests\GeckoServerExtension;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\SinglePageApp;
use Bveing\MBuddy\Ui\Template;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    public function testTextChange(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp();

            $label1 = new Component\Label(
                'L1',
            );

            $label2 = new Component\Label(
                'L2',
            );

            $comp = new class ($label1, $label2) implements Component {
                use Component\Trait\Refreshable;
                use Component\Trait\AutoId;
                public function __construct(private Component\Label $label1, private Component\Label $label2)
                {
                }

                public function template(): Template
                {
                    return Template::create(
                        "{{ A }} {{ B }}",
                        A: $this->label1,
                        B: $this->label2,
                    );
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();

                $elementId1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $label1->id()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $label2->id()));

                self::assertSame('L1', yield GeckoServerExtension::$driver->getElementText($elementId1));
                self::assertSame('L2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                $label1->setText('Hello');
                yield $app->refresh();

                $elementId1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $label1->id()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $label2->id()));

                self::assertSame('Hello', yield GeckoServerExtension::$driver->getElementText($elementId1));
                self::assertSame('L2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                $label2->setText('World');
                yield $app->refresh();

                $elementId1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $label1->id()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $label2->id()));

                self::assertSame('Hello', yield GeckoServerExtension::$driver->getElementText($elementId1));
                self::assertSame('World', yield GeckoServerExtension::$driver->getElementText($elementId2));


            } finally {
                yield $app->stop();
            }
        });
    }
}
