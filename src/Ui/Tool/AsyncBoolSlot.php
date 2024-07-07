<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Tool;

use Amp\Deferred;
use Amp\Promise;

class AsyncBoolSlot
{
    public function __construct()
    {
        $this->deferred = new Deferred();
    }

    public function accept(): void
    {
        $this->deferred->resolve(true);
    }

    public function reject(): void
    {
        $this->deferred->resolve(false);
    }

    /**
     * @return Promise<bool>
     */
    public function result(): Promise
    {
        return $this->deferred->promise();
    }

    /**
     * @var Deferred<bool>
     */
    private Deferred $deferred;
}
