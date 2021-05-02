<?php

namespace bviguier\MBuddy;

class MidiSyxBank
{
    public function __construct(string $folder)
    {
        $path = (new \SplFileInfo($folder))->getRealPath();
        if ($path === false) {
            throw new \Exception("Invalid folder [$folder].");
        }
        $this->folder = $path;
        foreach (new \DirectoryIterator($folder) as $file) {
            if ($file->getExtension() !== 'syx') {
                continue;
            }
            if ($file->getRealPath() === false) {
                continue;
            }
            [$id, $name] = explode('-', $file->getBasename('.syx'));
            $this->fileMap[(int) $id] = ['name' => $name, 'path' => $file->getRealPath()];
        }
    }

    public function save(int $id, string $name, string $data): bool
    {
        if($existingPatch = ($this->fileMap[$id] ?? null) ) {
            unlink($existingPatch['path']);
        }

        $strId = str_pad((string)$id, 3, '0', STR_PAD_LEFT);
        $this->fileMap[$id] = ['name' => $name, 'path' => $this->folder."/$strId-$name.syx"];

        return strlen($data) === file_put_contents($this->fileMap[$id]['path'], $data);
    }

    public function load(int $id): ?string
    {
        if (!isset($this->fileMap[$id])) {
            return null;
        }
        $data = file_get_contents($this->fileMap[$id]['path']);

        return $data === false ? null : $data;
    }

    private string $folder;
    /** @var array<int,array{name:string,path:string}>  */
    private array $fileMap = [];
}
