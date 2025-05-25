<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\App\Motif\Master\Registry;

use Amp\Promise;
use Bveing\MBuddy\App\Motif\Master\Registry;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Motif\SysEx;
use function Amp\call;

class FileCache implements Registry
{
    public function __construct(
        private string   $cacheDir,
        private Registry $delegate,
    ) {
        \assert(\is_dir($this->cacheDir));
        \assert(\is_writable($this->cacheDir));
    }

    public function get(Master\Id $id): ?Master
    {
        $cachePath = $this->cachePath($id);

        if (!\file_exists($cachePath)) {
            return null;
        }
        \assert(\is_readable($cachePath));
        $binary = \file_get_contents($cachePath);
        if ($binary === false) {
            // Corrupted file, delete it
            \unlink($cachePath);
            return null;
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
            return null;
        }

        return Master::fromBulkDumpBlocks(...$blocks);
    }

    public function refresh(Master\Id $id): Promise
    {
        return call(function() use ($id) {
            $master = yield $this->delegate->refresh($id);
            if ($master === null) {
                return null;
            }

            $filepath = $this->cachePath($master->id());
            \assert(!\file_exists($filepath) || \is_writable($filepath));
            $binary = '';
            foreach ($master->toBulkDumpBlocks() as $block) {
                $binary .= $block->toSysEx();
            }
            \file_put_contents($filepath, $binary);

            return $master;
        });
    }

    private function cachePath(Master\Id $id): string
    {
        return \sprintf("%s/%d.sysex", $this->cacheDir, $id->toInt());
    }
}
