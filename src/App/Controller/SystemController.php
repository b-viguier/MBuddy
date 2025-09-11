<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\Async\Server;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
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

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render(
            'system/index.html.twig',
            [
                'isServerEnabled' => $this->server->isRunning(),
                'app_env' => $this->getParameter('kernel.environment'),
            ],
        );
    }

    #[Route('/start/', name: 'server_start', methods: ['GET'])]
    public function startServer(Request $request): Response
    {
        if ($this->server->isRunning()) {
            throw new BadRequestHttpException(
                'Server is already running. Please stop it first.',
            );
        }

        $redirectUrl = $request->query->get('redirect_url')
            ?? $this->generateUrl('main_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        \assert(\is_string($redirectUrl));

        return $this->render(
            'system/ajaxCallAndRedirect.html.twig',
            [
                'title' => 'Starting Server 🚀',
                'target_url' => $this->generateUrl('server_start'),
                'redirect_url' => $this->replacePortInUrl(
                    $redirectUrl,
                    $this->server->port(),
                ),
            ]
        );
    }

    #[Route('/stop/', name: 'server_stop', methods: ['GET'])]
    public function stopServer(Request $request): Response
    {
        if (!$this->server->isRunning() || !($metaPort = $this->server->metaPort())) {
            throw new BadRequestHttpException(
                'Server is not running. Please start it first.',
            );
        }

        $redirectUrl = $request->query->get('redirect_url')
            ?? $this->generateUrl('main_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        \assert(\is_string($redirectUrl));

        return $this->render(
            'system/ajaxCallAndRedirect.html.twig',
            [
                'title' => 'Stopping Server 🛑',
                'target_url' => $this->generateUrl('server_stop'),
                'redirect_url' => $this->replacePortInUrl(
                    $redirectUrl,
                    $metaPort,
                ),
            ]
        );
    }

    #[Route('/restart/', name: 'server_restart', methods: ['GET'])]
    public function restartServer(Request $request): Response
    {
        if (!$this->server->isRunning() || !($metaPort = $this->server->metaPort())) {
            throw new BadRequestHttpException(
                'Server is not running. Please start it first.',
            );
        }

        // Redirection to final page, after start
        $redirectUrl = $request->query->get('redirect_url')
            ?? $this->generateUrl('main_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        // Redirection to start page, after stop
        $redirectUrl = $this->generateUrl(
            'system_server_start',
            ['redirect_url' => $redirectUrl],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return $this->redirectToRoute(
            'system_server_stop',
            ['redirect_url' => $redirectUrl]
        );
    }

    #[Route('/sf_env/list/', name: 'sf_env_list', methods: ['GET'])]
    public function listEnv(): Response
    {
        return $this->render(
            'system/env.html.twig',
            [
                'sf_envs' => self::VALID_ENVS,
                'current_env' => $this->getParameter('kernel.environment'),
            ],
        );
    }

    #[Route('/sf_env/change/{env}/', name: 'sf_env_change', methods: ['GET'])]
    public function changeEnv(string $env): Response
    {
        if (!\in_array($env, self::VALID_ENVS, true)) {
            throw new BadRequestHttpException(
                'Invalid environment. Valid options are: ' . \implode(', ', self::VALID_ENVS),
            );
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        \assert(\is_string($projectDir) && \is_dir($projectDir));
        \file_put_contents(
            Path::join($projectDir, '.env.local'),
            "APP_ENV=$env\n",
        );

        return $this->redirectToRoute('system_index');
    }

    private const VALID_ENVS = ['dev', 'staging'];

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
