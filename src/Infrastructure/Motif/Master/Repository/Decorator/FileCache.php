<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\Master\Repository\Decorator;

use Amp\Promise;
use Amp\Success;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Motif\SysEx;
use function Amp\call;

class FileCache implements Master\Repository
{
    public function __construct(
        private Master\Repository $delegate,
        private string $cacheDir,
    ) {
        \assert(\is_dir($this->cacheDir));
    }

    public function get(Master\Id $id): Promise
    {
        $cachePath = $this->cachePath($id);

        if (\file_exists($cachePath)) {
            \assert(\is_readable($cachePath));
            $binary = \file_get_contents($cachePath);
            if ($binary === false) {
                // Corrupted file, delete it
                \unlink($cachePath);
                return $this->get($id);
            }

            $offset = 0;
            $blocks = [];
            while($sysex = SysEx::extractFromBinaryString($binary, $offset)) {
                $blocks[] = SysEx\BulkDumpBlock::fromSysEx($sysex);
                $offset += $sysex->length();
            }

            if ($offset !== \strlen($binary)) {
                // Corrupted file, delete it
                \unlink($cachePath);
                return $this->get($id);
            }

            return new Success(Master::fromBulkDumpBlocks(...$blocks));
        }

        return call(function() use ($id, $cachePath) {
            $master = yield $this->delegate->get($id);
            $this->save($master, $cachePath);

            return $master;
        });
    }

    public function set(Master $master): Promise
    {
        $this->save($master, $this->cachePath($master->id()));

        return $this->delegate->set($master);
    }

    public function currentMasterId(): Promise
    {
        if ($this->currentId !== null) {
            return new Success($this->currentId);
        }

        return call(function() {
            $id = yield $this->delegate->currentMasterId();
            $this->currentId = $id;

            return $id;
        });
    }

    public function setCurrentMasterId(Master\Id $id): Promise
    {
        $this->currentId = $id;

        return $this->delegate->setCurrentMasterId($id);
    }

    public function clear(): void
    {
        foreach (\glob($this->cacheDir . '/*.sysex') ?: [] as $file) {
            \unlink($file);
        }
    }

    private ?Master\Id $currentId = null;

    private function cachePath(Master\Id $id): string
    {
        return \sprintf("%s/%d.sysex", $this->cacheDir, $id->toInt());
    }

    private function save(Master $master, string $filepath): void
    {
        \assert(!\file_exists($filepath) || \is_writable($filepath));
        $binary = '';
        foreach ($master->toBulkDumpBlocks() as $block) {
            $binary .= $block->toSysEx();
        }
        \file_put_contents($filepath, $binary);
    }
}
