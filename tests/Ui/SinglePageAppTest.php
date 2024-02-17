<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui;

use Bveing\MBuddy\Ui\SinglePageApp;
use Bveing\MBuddy\Ui\Component;
use Psr\Log\NullLogger;
use Amp\Loop;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Bveing\MBuddy\Tests\GeckoServerExtension;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;

use function Amp\delay;

class SinglePageAppTest extends TestCase
{
    public function testStartStop(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp(
                "MBuddy",
                new NullLogger(),
                __DIR__,
            );

            $comp = $this->createEmptyComponent();

            try {
                yield $app->start($comp);
                yield GeckoServerExtension::navigateToHomePage();

                $this->assertSame('MBuddy', yield GeckoServerExtension::$driver->getTitle());
                yield $app->stop();


                yield $app->start($comp);
                yield GeckoServerExtension::$driver->refresh();
                $title = yield GeckoServerExtension::$driver->getTitle();
                $this->assertSame('MBuddy', $title);
            } finally {
                yield $app->stop();
            }
        });
    }

    public function testConsoleLogsAreForwarded(): void
    {
        Loop::run(function() {
            $logger = new class () extends AbstractLogger {
                /**
                 * @var list<array{mixed,\Stringable|string,array<mixed>}>
                 */
                public array $logs = [];

                /**
                 * @param list<mixed> $context
                 * @return void
                 */
                public function log(mixed $level, \Stringable|string $message, array $context = []): void
                {
                    $this->logs[] = [$level, $message, $context];
                }
            };

            $app = new SinglePageApp(
                "MBuddy",
                $logger,
                __DIR__,
            );

            yield $app->start(
                new class () implements Component {
                    use Component\Trait\NonModifiable;
                    use Component\Trait\AutoId;
                    use Component\Trait\Childless;
                    public function render(): string
                    {
                        return <<<HTML
                            <script>
                            window.setTimeout(() => {
                                console.log('This is Log');
                                console.warn('This is Warning');
                                console.error('This is Error');
                            }, 100);
                            </script>
                            HTML;
                    }
                },
            );

            yield GeckoServerExtension::navigateToHomePage();
            yield GeckoServerExtension::$driver->refresh();

            try {
                yield delay(200);

                $this->assertContains(
                    ['info', '[Console] This is Log', []],
                    $logger->logs,
                );
                $this->assertContains(
                    ['warning', '[Console] This is Warning', []],
                    $logger->logs,
                );
                $this->assertContains(
                    ['error', '[Console] This is Error', []],
                    $logger->logs,
                );

            } finally {
                yield $app->stop();
            }
        });
    }

    public function testCanDownloadStaticFiles(): void
    {
        Loop::run(function() {
            $app = new SinglePageApp(
                "MBuddy",
                new NullLogger(),
                __DIR__,
            );

            $comp = $this->createEmptyComponent();
            $httpClient = HttpClientBuilder::buildDefault();

            try {
                yield $app->start($comp);
                $response = yield $httpClient->request(new Request('http://localhost:8383/' .basename(__FILE__)));
                $content = yield $response->getBody()->buffer();

                $this->assertSame(file_get_contents(__FILE__), $content);
            } finally {
                yield $app->stop();
            }
        });
    }

    private function createEmptyComponent(): Component
    {
        return new class () implements Component {
            use Component\Trait\NonModifiable;
            use Component\Trait\AutoId;
            use Component\Trait\Childless;
            public function render(): string
            {
                return '';
            }
        };
    }
}
