<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\App\Core\Preset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/presets')]
class PresetsController extends AbstractController
{
    public function __construct(
        private Preset\Repository $presetRepository,
    )
    {
    }

    #[Route('/', name: 'presets_list')]
    public function list(): Response
    {
        return $this->render(
            'presets/list.html.twig',
            [
                'presets' => $this->presetRepository->list(),
            ]
        );
    }

    #[Route('/new', name: 'presets_new')]
    public function new(): Response
    {
        $this->presetRepository->add(
            new Preset(
                Preset\Id::new(),
                'New Preset'
            )
        );
        return $this->redirectToRoute('presets_list');
    }

    #[Route('/delete/{id}', name: 'presets_delete')]
    public function delete(string $id): Response
    {
        $this->presetRepository->remove(Preset\Id::fromString($id));
        return $this->redirectToRoute('presets_delete_list');
    }

    #[Route('/delete', name: 'presets_delete_list')]
    public function deleteList(): Response
    {
        return $this->render(
            'presets/delete.html.twig',
            [
                'presets' => $this->presetRepository->list(),
            ]
        );
    }
}
