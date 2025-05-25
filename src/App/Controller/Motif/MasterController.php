<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller\Motif;

use Bveing\MBuddy\App\Motif\Master\NameRegistry;
use Bveing\MBuddy\Motif\Master;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Amp\Promise\wait;

#[Route('/motif/master', name: 'motif_master_')]
class MasterController extends AbstractController
{
    public function __construct(
        private NameRegistry $nameRegistry,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render(
            'motif/master/index.html.twig',
            [
                'masters' => $this->nameRegistry->all(),
            ]
        );
    }

    #[Route('/{id}/sync', name: 'sync')]
    public function sync(int $id): Response
    {
        $id = Master\Id::fromInt($id);

        wait($this->nameRegistry->sync($id));

        return $this->redirectToRoute('motif_master_index');
    }
}
