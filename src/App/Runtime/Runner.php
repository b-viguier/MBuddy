<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Runtime;

use Bveing\MBuddy\App\Server;
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
        $this->kernel->boot();

        $server = $this->kernel->getContainer()->get(Server::class);
        if (!$server instanceof Server) {
            throw new \RuntimeException('Server not found in container');
        }
        $server->run($this->request);

        $this->kernel->shutdown();

        return 0;
    }
}
