<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\App\ScoreStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stage', name: 'stage_')]
class StageController extends AbstractController
{
    public function __construct(
        private Preset\Repository $presetRepository,
        private ScoreStorage $scoreStorage,
    ) {
    }

    #[Route('/{id}/', name: 'show', methods: ['GET'])]
    public function stage(string $id): Response
    {
        $id = Preset\Id::fromString($id);
        $preset = $this->presetRepository->get($id);
        if ($preset === null) {
            throw $this->createNotFoundException();
        }
        [$previous, $next] = $this->presetRepository->surrounding($id);

        return $this->render(
            'stage/show.html.twig',
            [
                'preset' => $preset,
                'nextUrl' => $next ? $this->generateUrl(
                    'stage_show',
                    ['id' => $next->id()->toString()],
                ) : null,
                'previousUrl' => $previous ? $this->generateUrl(
                    'stage_show',
                    ['id' => $previous->id()->toString()],
                ) : null,
                'scoreUrl' => $this->scoreStorage->exists($preset->id()) ? $this->generateUrl(
                    'score_show',
                    ['id' => $preset->id()->toString()],
                ) : null
            ]
        );
    }

    #[Route('/', name: 'start', methods: ['GET'])]
    public function start(): Response
    {
        foreach ($this->presetRepository->list() as $preset) {
            return $this->redirectToRoute(
                'stage_show',
                ['id' => $preset->id()->toString()]
            );
        }

        throw $this->createNotFoundException();
    }
}
