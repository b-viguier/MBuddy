<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\App\Server;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/system', name: 'system_')]
class SystemController extends AbstractController
{
    public function __construct(
        private Server $server,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render(
            'system/index.html.twig',
            [
                'isServerEnabled' => $this->server->isRunning(),
            ],
        );
    }

    #[Route('/start', name: 'server_start', methods: ['GET'])]
    public function startServer(): Response
    {
        if ($this->server->isRunning()) {
            throw new BadRequestHttpException(
                'Server is already running. Please stop it first.',
            );
        }

        return $this->render(
            'system/ajaxCallAndRedirect.html.twig',
            [
                'title' => 'Starting Server 🚀',
                'target_url' => $this->generateUrl('system_server_start'),
                'target_headers' => ['MBUDDY_START_PHP_SERVER' => 'true'],
                'redirect_url' => $this->replacePortInUrl(
                    $this->generateUrl('system_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                    $this->server->port(),
                ),
            ]
        );
    }

    #[Route('/stop', name: 'server_stop', methods: ['GET'])]
    public function stopServer(Request $request): Response
    {
        if (!$this->server->isRunning() || !($metaPort = $this->server->metaPort())) {
            throw new BadRequestHttpException(
                'Server is not running. Please start it first.',
            );
        }

        if ($request->query->has('ajax')) {
            $this->server->stop();

            return new Response();
        }


        return $this->render(
            'system/ajaxCallAndRedirect.html.twig',
            [
                'title' => 'Stopping Server 🛑',
                'target_url' => $this->generateUrl('system_server_stop', ['ajax' => 'true']),
                'redirect_url' => $this->replacePortInUrl(
                    $this->generateUrl('system_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                    $metaPort,
                ),
            ]
        );
    }

    private function replacePortInUrl(string $url, int $newPort): string
    {
        return \preg_replace(
            '#:(\d+)/#',
            ":{$newPort}/",
            $url,
        ) ?? throw new \Error(
            "Failed to replace port in URL: $url",
        );
    }
}
