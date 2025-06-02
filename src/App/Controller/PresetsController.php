<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\App\ScoreStorage;
use Bveing\MBuddy\Motif\Master;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/presets', name: 'presets_')]
class PresetsController extends AbstractController
{
    public function __construct(
        private Preset\Repository $presetRepository,
        private ScoreStorage $scoreStorage,
    ) {
    }

    #[Route('/', name: 'list')]
    public function list(): Response
    {
        return $this->render(
            'presets/list.html.twig',
            [
                'presets' => $this->presetRepository->list(),
            ]
        );
    }

    #[Route('/new', name: 'new')]
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

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(string $id): Response
    {
        $id = Preset\Id::fromString($id);
        $this->presetRepository->remove($id);
        $this->scoreStorage->delete($id);

        return $this->redirectToRoute('presets_delete_list');
    }

    #[Route('/delete', name: 'delete_list')]
    public function deleteList(): Response
    {
        return $this->render(
            'presets/delete.html.twig',
            [
                'presets' => $this->presetRepository->list(),
            ]
        );
    }

    #[Route('/sort', name: 'sort_list', methods: ['GET'])]
    public function sortList(): Response
    {
        return $this->render(
            'presets/sort.html.twig',
            [
                'presets' => $this->presetRepository->list(),
            ]
        );
    }

    #[Route('/sort', name: 'sort', methods: ['POST'])]
    public function sort(Request $request): Response
    {
        $ids = $request->request->all('presets');
        $this->presetRepository->sort(
            ...\array_map(
                static fn($id) => Preset\Id::fromString($id),
                $ids,
            )
        );

        return $this->redirectToRoute('presets_list');
    }

    #[Route('/copy', name: 'copy_list')]
    public function copyList(): Response
    {
        return $this->render(
            'presets/copy.html.twig',
            [
                'presets' => $this->presetRepository->list(),
            ]
        );
    }

    #[Route('/copy/{id}', name: 'copy')]
    public function copy(string $id): Response
    {
        $id = Preset\Id::fromString($id);
        if(null === $preset = $this->presetRepository->get($id)) {
            throw $this->createNotFoundException('Preset not found');
        }

        $newPreset = $preset->with(
            id: Preset\Id::new(),
            name: $preset->name() . ' (copy)',
        );
        $this->presetRepository->add($newPreset);
        $this->scoreStorage->copy($preset->id(), $newPreset->id());

        return $this->redirectToRoute('presets_copy_list');
    }

    #[Route('/{id}', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        $preset = $this->presetRepository->get(Preset\Id::fromString($id));
        if(null === $preset) {
            throw $this->createNotFoundException('Preset not found');
        }

        if ($request->isMethod('POST')) {

            $preset = $preset->with(
                name: (string) $request->request->get('name'),
                masterId: $request->request->get('masterId') !== '' ? Master\Id::fromInt($request->request->getInt('masterId')) : null,
                scoreTxt: (string) $request->request->get('scoreTxt'),
            );
            $this->presetRepository->save($preset);

            $file = $request->files->get('scoreImg');
            if($file instanceof UploadedFile) {
                $this->scoreStorage->store($preset->id(), $file);
            } elseif ($request->request->get('scoreImgDelete', false)) {
                $this->scoreStorage->delete($preset->id());
            }
        }

        return $this->render(
            'presets/edit.html.twig',
            [
                'preset' => $preset,
                'scoreUrl' => $this->scoreStorage->exists($preset->id()) ? $this->generateUrl(
                    'score_show',
                    ['id' => $preset->id()->toString()],
                ) : null,
            ]
        );
    }
}
