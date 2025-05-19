<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/assets', name: 'assets_')]
class AssetsController extends AbstractController
{
    #[Route('/js/{file}.js', name: 'js')]
    public function getJs(string $file): Response
    {
        return $this->handleFile("js/$file.js", 'application/javascript');
    }

    #[Route('/css/{file}.css', name: 'css')]
    public function getCss(string $file): Response
    {
        return $this->handleFile("css/$file.css", 'text/css');
    }

    #[Route('/images/{file}', name: 'images')]
    public function getImages(string $file): Response
    {
        return $this->handleFile("images/$file");
    }

    #[Route('/css/fonts/{file}', name: 'fonts')]
    public function getFonts(string $file): Response
    {
        return $this->handleFile("css/fonts/$file");
    }

    private function handleFile(string $path, ?string $contentType = null): Response
    {
        static $baseDir = null;
        $baseDir ??= $this->getParameter('kernel.project_dir');
        \assert(is_string($baseDir));
        $file =  "$baseDir/public/assets/$path";
        if (!file_exists($file)) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse(
            $file,
            headers: $contentType === null ? [] : ['Content-Type' => $contentType],
            autoEtag: true,
            autoLastModified: true,
        );

        $response->headers->addCacheControlDirective('max-age', strval(3600 * 4));

        return $response;
    }
}
