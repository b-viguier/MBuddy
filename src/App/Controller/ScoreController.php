<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\App\ScoreStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/score', name: 'score_')]
class ScoreController extends AbstractController
{
    public function __construct(
        private ScoreStorage $scoreStorage,
    )
    {
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function score(string $id): Response
    {
        $file = $this->scoreStorage->find(Preset\Id::fromString($id));
        if ($file === null) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse(
            $file->getRealPath(),
            autoEtag: true,
            autoLastModified: true,
        );
    }
}
