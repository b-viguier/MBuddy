<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller\Motif;

use Bveing\MBuddy\App\Motif\Master\NameRegistry;
use Bveing\MBuddy\App\Motif\Master\Registry;
use Bveing\MBuddy\Motif\Master;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Amp\Promise\wait;

#[Route('/motif/master', name: 'motif_master_')]
class MasterController extends AbstractController
{
    public function __construct(
        private NameRegistry $nameRegistry,
        private Registry $registry,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render(
            'motif/master/index.html.twig',
            [
                'masters' => $this->nameRegistry->all(),
            ]
        );
    }

    #[Route('/{id}/sync/', name: 'sync')]
    public function sync(int $id): Response
    {
        $id = Master\Id::fromInt($id);

        wait($this->nameRegistry->sync($id));

        return $this->redirectToRoute('motif_master_edit', ['id' => $id->toInt()]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(int $id): Response
    {
        $id = Master\Id::fromInt($id);
        $master = $this->registry->get($id);
        $name = $this->nameRegistry->get($id);

        return $this->render(
            'motif/master/edit.html.twig',
            [
                'id' => $id->toInt(),
                'name' => $name,
                'master' => $master,
            ]
        );
    }

    #[Route('/{id}/select/', name: 'select', methods: ['POST'])]
    public function select(int $id): JsonResponse
    {
        $masterId = Master\Id::fromInt($id);
        try {
            wait($this->registry->select($masterId));
        } catch(\Throwable $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
