<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\App\Motif\Master\Registry;

use Amp\Promise;
use Bveing\MBuddy\App\Motif\Master\Registry;
use Bveing\MBuddy\Motif\Master;
use function Amp\call;

class InMemoryCache implements Registry
{
    public function __construct(
        private Registry $delegate,
    ) {
    }

    public function get(Master\Id $id): ?Master
    {
        return $this->masters[$id->toInt()] ?? $this->delegate->get($id);
    }

    public function refresh(Master\Id $id): Promise
    {
        return call(function() use ($id) {
            unset($this->masters[$id->toInt()]);
            $master = yield $this->delegate->refresh($id);
            if ($master === null) {
                return null;
            }

            return $this->masters[$id->toInt()] = $master;
        });
    }

    /**
     * @var array<int, Master>
     */
    private array $masters = [];
}
