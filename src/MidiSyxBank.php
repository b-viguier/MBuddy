<?php

namespace bviguier\MBuddy;

class MidiSyxBank
{
    public const MAX_SIZE = 128;

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

    public function save(string $name, string $data): int
    {
        $id = $this->searchIdFromName($name);
        if ($id === null) {
            for ($i = 0; $i < self::MAX_SIZE; ++$i) {
                if (!isset($this->fileMap[$i])) {
                    $id = $i;
                    break;
                }
            }
            if ($id === null) {
                throw new \Exception("Cannot save [$name]: no available slot.");
            }
            $strId = str_pad((string)$id, 3, '0', STR_PAD_LEFT);
            $this->fileMap[$id] = ['name' => $name, 'path' => $this->folder."/$strId-$name.syx"];
        }

        file_put_contents($this->fileMap[$id]['path'], $data);

        return $id;
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

    private function searchIdFromName(string $name): ?int
    {
        foreach ($this->fileMap as $id => $item) {
            if ($item['name'] === $name) {
                return $id;
            }
        }

        return null;
    }
}
