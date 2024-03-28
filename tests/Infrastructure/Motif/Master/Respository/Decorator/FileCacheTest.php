<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Infrastructure\Motif\Master\Respository\Decorator;

use Amp\Success;
use Bveing\MBuddy\Infrastructure\Motif\Master\Repository\Decorator\FileCache;
use Bveing\MBuddy\Motif\Master;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;

class FileCacheTest extends TestCase
{
    public function setUp(): void
    {
        $this->cacheDir = \uniqid(\sys_get_temp_dir() . '/cache/MBuddyFileCacheTest/', true) . '/';
        \mkdir($this->cacheDir, recursive: true);
    }

    public function tearDown(): void
    {
        if (\is_dir($this->cacheDir)) {
            $content = \scandir($this->cacheDir);
            if ($content === false) {
                throw new \RuntimeException("Failed to read cache directory: $this->cacheDir");
            }
            foreach ($content as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                \unlink($this->cacheDir . $file);
            }
            \rmdir($this->cacheDir);
        }
    }

    public function testCurrentMasterId(): void
    {
        $repository = $this->createMock(Master\Repository::class);
        $repository->expects(self::once())
            ->method('currentMasterId')
            ->willReturn(new Success(Master\Id::fromInt(42)));

        $cache = new FileCache($repository, $this->cacheDir);

        self::assertEmpty(\glob("$this->cacheDir/*.*"));

        self::assertSame(42, wait($cache->currentMasterId())->toInt());
        self::assertSame(42, wait($cache->currentMasterId())->toInt());
        self::assertSame(42, wait($cache->currentMasterId())->toInt());
    }

    public function testSetCurrentMasterId(): void
    {
        $repository = $this->createMock(Master\Repository::class);
        $repository->expects(self::once())
            ->method('setCurrentMasterId')
            ->with(Master\Id::fromInt(42))
            ->willReturn(new Success(1));
        $repository->expects(self::never())
            ->method('currentMasterId');

        $cache = new FileCache($repository, $this->cacheDir);

        self::assertSame(1, wait($cache->setCurrentMasterId(Master\Id::fromInt(42))));
        self::assertSame(42, wait($cache->currentMasterId())->toInt());
        self::assertSame(42, wait($cache->currentMasterId())->toInt());
        self::assertEmpty(\glob("$this->cacheDir/*.*"));

    }

    public function testGet(): void
    {
        $repository = $this->createMock(Master\Repository::class);
        $repository->expects(self::once())
            ->method('get')
            ->with(Master\Id::fromInt(42))
            ->willReturn(new Success(Master::default()->with(id: Master\Id::fromInt(42))));

        $cache = new FileCache($repository, $this->cacheDir);

        self::assertSame(0, $this->countCacheFiles());

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());
    }

    public function testSet(): void
    {
        $repository = $this->createMock(Master\Repository::class);
        $repository->expects(self::once())
            ->method('set')
            ->with(Master::default()->with(id: Master\Id::fromInt(42)))
            ->willReturn(new Success());
        $repository->expects(self::never())
            ->method('get');

        $cache = new FileCache($repository, $this->cacheDir);

        self::assertSame(0, $this->countCacheFiles());

        wait($cache->set(Master::default()->with(id: Master\Id::fromInt(42))));
        self::assertCount(1, \glob("$this->cacheDir/*.*") ?: []);

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());
    }

    public function testClear(): void
    {
        $repository = $this->createMock(Master\Repository::class);
        $repository->expects(self::exactly(2))
            ->method('get')
            ->with(Master\Id::fromInt(42))
            ->willReturn(new Success(Master::default()->with(id: Master\Id::fromInt(42))));

        $cache = new FileCache($repository, $this->cacheDir);

        self::assertSame(0, $this->countCacheFiles());

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());

        $cache->clear();
        self::assertSame(0, $this->countCacheFiles());

        self::assertSame(42, wait($cache->get(Master\Id::fromInt(42)))?->id()->toInt());
        self::assertSame(1, $this->countCacheFiles());
    }
    private string $cacheDir;

    private function countCacheFiles(): int
    {
        return \count(\glob("$this->cacheDir/*.*") ?: []);
    }
}
