<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Runtime;

use Bveing\MBuddy\Async\LoopRunner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Runtime\RunnerInterface;

class Runner implements RunnerInterface
{
    public function __construct(
        private KernelInterface $kernel,
        private ?Request $request,
    ) {
    }

    public function run(): int
    {
        LoopRunner::globalInit();
        $this->kernel->boot();

        $loopRunner = $this->kernel->getContainer()->get(LoopRunner::class);
        if (!$loopRunner instanceof LoopRunner) {
            throw new \RuntimeException('LoopRunner not found in container');
        }

        $loopRunner->run($this->request);

        $this->kernel->shutdown();

        return 0;
    }
}
