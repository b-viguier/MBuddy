<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core\Preset;

use Amp\Promise;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;
use function Amp\call;

interface Repository
{
    public function get(Preset\Id $id): ?Preset;

    public function list(): iterable;

    public function add(Preset $preset): bool;

    public function remove(Preset\Id $id): bool;
}
