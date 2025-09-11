<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\Async\Server;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/server', name: 'server_')]
class ServerController extends AbstractController
{
    public const MBUDDY_BASE_PATH = '/MBuddy';

    public function __construct(
        private Server $server,
    ) {
    }

    public static function isStartUri(string $uri): bool
    {
        return $uri === self::MBUDDY_BASE_PATH . '/server/start/';
    }

    #[Route('/start/', name: 'start', methods: ['GET'])]
    public function start(): void
    {
        // This method is intentionally left blank.
        // The actual server start logic is handled in the Runtime class.
        throw new \RuntimeException('This method should not be called directly.');
    }

    #[Route('/stop/', name: 'stop', methods: ['GET'])]
    public function stop(): Response
    {
        $this->server->stop();

        return new Response('Server stopped', Response::HTTP_OK);
    }
}
