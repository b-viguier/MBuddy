<?php

namespace bviguier\MBuddy;

use bviguier\RtMidi;

class MidiSyxBank
{
    public function __construct(string $folder)
    {
        $this->folder = (new \SplFileInfo($folder))->getRealPath();
        foreach (new \DirectoryIterator($folder) as $file) {
            if ($file->getExtension() !== 'syx') {
                continue;
            }
            [$id, $name] = explode('-', $file->getBasename('.syx'));
            $this->fileMap[(int)$id] = ['name' => $name, 'path' => $file->getRealPath()];
        }
    }

    public function save(string $name, RtMidi\Message $msg): int
    {
        $id = $this->name2id($name);
        if ($id === null) {
            for ($i = 0; $i < 128; ++$i) {
                if (!isset($this->fileMap[$i])) {
                    $id = $i;
                    break;
                }
            }
            if ($id === null) {
                throw new \Exception("Cannot save [$name]: no available slot.");
            }
            $strId = str_pad($id, 3, '0', STR_PAD_LEFT);
            $this->fileMap[$id] = ['name' => $name, 'path' => $this->folder."/$strId-$name.syx"];
        }

        file_put_contents($this->fileMap[$id]['path'], $msg->toBinString());

        return $id;
    }

    public function setBankMSB(int $msb): void
    {
        $this->bankMSB = $msb;
    }

    public function setBankLSB(int $lsb): void
    {
        $this->bankLSB = $lsb;
    }

    public function load(int $id): ?RtMidi\Message
    {
        if ($this->bankLSB !== 0 || $this->bankMSB !== 0 || !isset($this->fileMap[$id])) {
            return null;
        }

        return RtMidi\Message::fromBinString(file_get_contents($this->fileMap[$id]['path']));
    }

    private string $folder;
    private array $fileMap = [];
    private int $bankLSB = 0;
    private int $bankMSB = 0;

    private function name2id(string $name): ?int
    {
        foreach ($this->fileMap as $id => $item) {
            if ($item['name'] === $name) {
                return $id;
            }
        }

        return null;
    }
}
