<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui\Component;

use Amp\Loop;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Tests\GeckoServerExtension;
use Bveing\MBuddy\Tests\Support\SpyReceiver;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Component\Select;
use Bveing\MBuddy\Ui\SinglePageApp;
use Bveing\MBuddy\Ui\Template;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    public function testCallbackWhenClicked(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp();

            $slot1 = new SpyReceiver();
            $slot2 = new SpyReceiver();

            $select1 = Select::create()->set(
                options: [
                    'keyA' => 'A',
                    'keyB' => 'B',
                    'keyC' => 'C',
                ],
                currentIndex: 'keyB',
            );
            $select2 = Select::create()->set(
                options: [
                    'key1' => '1',
                    'key2' => '2',
                    'key3' => '3',
                ],
                currentIndex: 'key2',
            );

            Siglot::connect2(
                \Closure::fromCallable([$select1, 'selected']),
                \Closure::fromCallable([$slot1, 'slot']),
            );
            Siglot::connect2(
                \Closure::fromCallable([$select2, 'selected']),
                \Closure::fromCallable([$slot2, 'slot']),
            );

            $comp = new class ($select1, $select2) implements Component {
                use Component\Trait\Refreshable;
                use Component\Trait\AutoId;
                use EmitterHelper;

                public function __construct(private Select $select1, private Select $select2)
                {
                }

                public function template(): Template
                {
                    return Template::create(
                        "{{ A }} {{ B }}",
                        A: $this->select1,
                        B: $this->select2,
                    );
                }
            };


            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();

                self::assertCount(0, $slot1->calls);
                self::assertCount(0, $slot2->calls);

                $elementId1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $select1->id()));
                $elementId2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s', $select2->id()));

                self::assertSame("A\nB\nC", yield GeckoServerExtension::$driver->getElementText($elementId1));
                self::assertSame("1\n2\n3", yield GeckoServerExtension::$driver->getElementText($elementId2));

                $selectedOption1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[selected]', $select1->id()));
                $selectedOption2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[selected]', $select2->id()));

                self::assertSame("B", yield GeckoServerExtension::$driver->getElementText($selectedOption1));
                self::assertSame("2", yield GeckoServerExtension::$driver->getElementText($selectedOption2));
                self::assertSame("B", $select1->text());
                self::assertSame("2", $select2->text());

                $otherOption1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[value="keyA"]', $select1->id()));
                $otherOption2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[value="key3"]', $select2->id()));

                self::assertSame("A", yield GeckoServerExtension::$driver->getElementText($otherOption1));
                self::assertSame("3", yield GeckoServerExtension::$driver->getElementText($otherOption2));

                yield GeckoServerExtension::$driver->clickElement($otherOption1);
                yield GeckoServerExtension::$driver->clickElement($otherOption2);

                self::assertSame([['A', 'keyA']], $slot1->calls);
                self::assertSame([['3', 'key3']], $slot2->calls);

                $selectedOption1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[selected]', $select1->id()));
                $selectedOption2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[selected]', $select2->id()));

                self::assertSame("A", yield GeckoServerExtension::$driver->getElementText($selectedOption1));
                self::assertSame("3", yield GeckoServerExtension::$driver->getElementText($selectedOption2));

                self::assertSame("A", $select1->text());
                self::assertSame("3", $select2->text());

                $select1->selectByIndex('keyC');
                $select2->selectByIndex('key1');

                $selectedOption1 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[selected]', $select1->id()));
                $selectedOption2 = yield GeckoServerExtension::$driver->findElement(\sprintf('#%s > option[selected]', $select2->id()));

                self::assertSame("C", yield GeckoServerExtension::$driver->getElementText($selectedOption1));
                self::assertSame("1", yield GeckoServerExtension::$driver->getElementText($selectedOption2));

                self::assertSame("C", $select1->text());
                self::assertSame("1", $select2->text());

            } finally {
                yield $app->stop();
            }
        });
    }
}
