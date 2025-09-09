<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\DependencyInjection\Compiler;

use Bveing\MBuddy\App\Runtime\BackgroundService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BackgroundServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $bgDefinition = $container->findDefinition(BackgroundService::class);

        $taggedServices = $container->findTaggedServiceIds('mbuddy.background');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $startFn = $attributes['start'] ?? null;
                $stopFn = $attributes['stop'] ?? null;
                $bgDefinition->addMethodCall('addService', [$container->getDefinition($id), $startFn, $stopFn]);
            }
        }
    }
}
