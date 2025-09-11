<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Runtime;

use Bveing\MBuddy\App\Controller\ServerController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

class Runtime extends SymfonyRuntime
{
    public function getRunner(?object $application): RunnerInterface
    {
        if (
            $application instanceof KernelInterface
            && ServerController::isStartUri($_SERVER['REQUEST_URI'] ?? '')
        ) {
            return new Runner($application, Request::createFromGlobals());
        }

        return parent::getRunner($application);
    }
}
