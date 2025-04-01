<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/assets')]
class AssetsController extends AbstractController
{
    #[Route('/js/{file}.js', name: 'assets_js')]
    public function getJs(string $file): Response
    {
        return $this->handleFile("js/$file.js");
    }

    #[Route('/css/{file}.css', name: 'assets_css')]
    public function getCss(string $file): Response
    {
        return $this->handleFile("css/$file.css");
    }

    #[Route('/images/{file}', name: 'assets_images')]
    public function getImages(string $file): Response
    {
        return $this->handleFile("images/$file");
    }

    #[Route('/css/fonts/{file}', name: 'assets_fonts')]
    public function getFonts(string $file): Response
    {
        return $this->handleFile("css/fonts/$file");
    }

    private function handleFile(string $path): Response
    {
        $file = $this->getParameter('kernel.project_dir') . '/public/assets/' . $path;
        if (!file_exists($file)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($file);
    }
}
