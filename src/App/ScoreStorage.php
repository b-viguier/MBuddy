<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App;

use Bveing\MBuddy\App\Core\Preset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScoreStorage
{
    public function __construct(
        private string $basePath,
    ) {
    }

    public function store(Preset\Id $id, UploadedFile $uploadedFile): void
    {
        $this->delete($id);
        $uploadedFile->move($this->basePath, $id->toString() . '.' . $uploadedFile->guessExtension());
    }

    public function copy(Preset\Id $src, Preset\Id $dst): bool
    {
        $file = $this->find($src);
        if ($file === null) {
            return false;
        }

        return copy($file->getRealPath(), $this->basePath . DIRECTORY_SEPARATOR . $dst->toString() . '.' . $file->getExtension());
    }
    
    public function find(Preset\Id $id): ?\SplFileInfo
    {
        $pattern = $this->basePath . DIRECTORY_SEPARATOR . $id->toString() . '.*';
        $files = glob($pattern);
        if($files === false || count($files) !== 1) {
            return null;
        }
        
        return new \SplFileInfo(reset($files));
    }

    public function exists(Preset\Id $id): bool
    {
        return $this->find($id) !== null;
    }
    
    public function delete(Preset\Id $id): bool
    {
        $file = $this->find($id);
        if ($file === null) {
            return false;
        }
        
        return unlink($file->getPathname());
    }
}