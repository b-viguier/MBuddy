<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Async;

use Amp\Loop;

/**
 * Inspired from https://github.com/amphp/amp/pull/309/files
 */
class NestableNativeDriver extends Loop\NativeDriver
{
    public function __construct()
    {
        parent::__construct();

        $this->isEmpty = (fn() => $this->isEmpty()) // @phpstan-ignore-line
            ->bindTo($this, Loop\Driver::class);
        $this->tick = (fn() => $this->tick())   // @phpstan-ignore-line
            ->bindTo($this, Loop\Driver::class);
    }

    #[\Override]
    public function run()
    {
        $nesting = $this->nesting;
        $this->setNesting($this->nesting + 1);

        try {
            while ($nesting < $this->nesting) {
                if (($this->isEmpty)()) {
                    return;
                }
                ($this->tick)();
            }
        } finally {
            $this->setNesting(\min($this->nesting, $nesting));
        }
    }

    #[\Override]
    public function stop()
    {
        $this->setNesting(\max(0, $this->nesting - 1));
    }


    /**
     * Do net edit this value directly, use setNesting() instead.
     */
    private int $nesting = 0;

    /**
     * Change nesting level and keep it in sync with Driver::running.
     */
    private function setNesting(int $nesting): void
    {
        $newRunningState = $nesting > 0;
        if ($newRunningState !== ($this->nesting > 0)) {
            // We are changing state, let's keep Driver::running in sync.
            (function(bool $running) {
                $this->running = $running;  // @phpstan-ignore-line
            })->bindTo($this, Loop\Driver::class)($newRunningState);
        }

        $this->nesting = $nesting;
    }

    private \Closure $isEmpty;
    private \Closure $tick;
}
