<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use Amp\Loop;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Tests\GeckoServerExtension;
use Bveing\MBuddy\Tests\Support\SpyReceiver;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Component\Button;
use Bveing\MBuddy\Ui\SinglePageApp;
use Bveing\MBuddy\Ui\Style\Icon;
use Bveing\MBuddy\Ui\Template;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    public function testCallbackWhenClicked(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp();

            $slot1 = new SpyReceiver();
            $slot2 = new SpyReceiver();

            $button1 = Button::create()->set(
                label: 'B1',
            );
            $button2 = Button::create()->set(
                label: 'B2',
            );

            Siglot::connect0(
                \Closure::fromCallable([$button1, 'clicked']),
                \Closure::fromCallable([$slot1, 'slot']),
            );
            Siglot::connect0(
                \Closure::fromCallable([$button2, 'clicked']),
                \Closure::fromCallable([$slot2, 'slot']),
            );

            $comp = new class ($button1, $button2) implements Component {
                use Component\Trait\Refreshable;
                use Component\Trait\AutoId;
                use EmitterHelper;

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

                self::assertCount(0, $slot1->calls);
                self::assertCount(0, $slot2->calls);

                $elementId1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $button1->id()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $button2->id()));

                self::assertSame('B1', yield GeckoServerExtension::$driver->getElementText($elementId1));
                self::assertSame('B2', yield GeckoServerExtension::$driver->getElementText($elementId2));

                yield GeckoServerExtension::$driver->clickElement($elementId1);

                self::assertCount(1, $slot1->calls);
                self::assertCount(0, $slot2->calls);

                yield GeckoServerExtension::$driver->clickElement($elementId2);

                self::assertCount(1, $slot1->calls);
                self::assertCount(1, $slot2->calls);


            } finally {
                yield $app->stop();
            }
        });
    }

    public function testClickWithIcon(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp();

            $button = Button::create()->set(
                icon: Icon::X(),
            );

            $slot = new SpyReceiver();
            Siglot::connect0(
                \Closure::fromCallable([$button, 'clicked']),
                \Closure::fromCallable([$slot, 'slot']),
            );

            try {
                yield $app->start($button);
                yield GeckoServerExtension::navigateToHomePage();

                $elementId = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $button->id()));
                yield GeckoServerExtension::$driver->clickElement($elementId);

                self::assertCount(1, $slot->calls);
            } finally {
                yield $app->stop();
            }
        });
    }
}
