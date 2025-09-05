<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Bveing\MBuddy\DevTools\FileTree;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dev-tools', name: 'dev_tools_')]
class DevToolsController extends AbstractController
{
    /**
     * @param list<string> $synchronizedFiles
     */
    public function __construct(
        private string $projectDir,
        private array $synchronizedFiles,
    ) {
    }

    #[Route('/dump/', name: 'dump', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $fileTree = new FileTree();
        foreach ($this->synchronizedFiles as $relativePath) {
            $fileTree->addFilesFromFileSystem($this->projectDir, $relativePath);
        }

        return new JsonResponse($fileTree->dumpToArray());
    }

    #[Route('/delete/', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {
        $content = $request->getContent();
        \assert(\is_string($content));
        $files = \json_decode($content, true);
        if (!\is_array($files)) {
            return new JsonResponse(['error' => 'Invalid file list'], Response::HTTP_BAD_REQUEST);
        }

        $deletedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            $absolutePath = Path::join($this->projectDir, $file);
            if (!\file_exists($absolutePath)) {
                $errors[] = "File does not exist: $file";
                continue;
            }
            if (\is_file($absolutePath)) {
                if (\unlink($absolutePath)) {
                    $deletedFiles[] = $file;
                } else {
                    $errors[] = "Failed to delete file: $file";
                }
                continue;
            }
            if (\is_dir($absolutePath)) {
                if (\rmdir($absolutePath)) {
                    $deletedFiles[] = $file;
                } else {
                    $errors[] = "Failed to delete directory (not empty?): $file";
                }
                continue;
            }

            $errors[] = "Not a file or directory: $file";
        }

        return new JsonResponse([
            'deleted' => $deletedFiles,
            'errors' => $errors,
        ]);
    }

    #[Route('/upload/', name: 'upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $relativePath = $request->request->get('relative_path');
        if (!\is_string($relativePath)) {
            return new JsonResponse(['error' => 'Invalid relative_path'], Response::HTTP_BAD_REQUEST);
        }
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $absolutePath = Path::join($this->projectDir, $relativePath);
        $dstDir = Path::getDirectory($absolutePath);
        if (! \file_exists($dstDir)) {
            \mkdir($dstDir, recursive: true);
        }
        $file->move($dstDir, $absolutePath);

        return new JsonResponse(['status' => 'ok']);
    }
}
