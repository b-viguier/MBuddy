<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Motif\Master;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;
use function Amp\call;

class NameRegistry
{
    public function __construct(
        private Registry $registry,
    ) {
    }

    public function get(Master\Id $id): string
    {
        return $this->name($id, $this->registry->get($id));
    }

    /**
     * @return Promise<string>
     */
    public function sync(Master\Id $id): Promise
    {
        return call(function() use ($id) {
            return $this->name($id, yield $this->registry->refresh($id));
        });
    }

    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        $names = [];
        foreach (Master\Id::all() as $id) {
            $names[$id->toInt()] = $this->get($id);
        }

        return $names;
    }

    private function name(Master\Id $id, ?Master $master): string
    {
        return $master?->name() ?? "Unknown {$id->toInt()}";
    }
}
