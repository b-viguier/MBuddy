<?php

declare(strict_types=1);

namespace Bveing\MBuddy\DevTools;

use Symfony\Component\Filesystem\Path;

/**
 * @phpstan-type FileTreeDump array<string, mixed>
 */
class FileTree
{
    /**
     * @param FileTreeDump $files
     */
    public static function fromArrayDump(array $files): self
    {
        $new = new self();
        $new->tree = $files;
        return $new;
    }

    /**
     * @return FileTreeDump
     */
    public function dumpToArray(): array
    {
        return $this->tree;
    }

    /**
     * @param array<string,string> $files
     */
    public function addFilesWithHash(string $relativeFolder, array $files): void
    {
        $canonicalFolder = Path::canonicalize($relativeFolder);
        $parts = $canonicalFolder === '' ? [] : \explode(\DIRECTORY_SEPARATOR, $canonicalFolder);
        $tree = $files;
        foreach (\array_reverse($parts) as $part) {
            $tree = [$part => $tree];
        }
        $this->mergeTree($tree);
    }

    public function addFilesFromFileSystem(string $root, string $relativePath): void
    {
        $absolutePath = Path::makeAbsolute($relativePath, $root);
        if (!\file_exists($absolutePath)) {
            throw new \InvalidArgumentException('File does not exist: ' . $relativePath);
        }

        if (\is_file($absolutePath)) {
            if (false === $hash = \hash_file('sha256', $absolutePath)) {
                throw new \RuntimeException('Could not hash file: ' . $relativePath);
            }
            $this->addFilesWithHash(
                Path::getDirectory($relativePath),
                [\basename($relativePath) => $hash],
            );

            return;
        }

        $files = [];
        $folders = [];
        /** @var \Iterator<\SplFileInfo> $fileIterator */
        $fileIterator = new \FilesystemIterator($absolutePath, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
        foreach($fileIterator as $file) {
            if ($file->isDir()) {
                $folders[] = $file->getFilename();
            } else {
                if (false === $hash = \hash_file('sha256', $file->getPathname())) {
                    throw new \RuntimeException('Could not hash file: ' . Path::makeRelative($file->getPathname(), $root));
                }
                $files[$file->getFilename()] = $hash;
            }
        }
        $this->addFilesWithHash($relativePath, $files);
        foreach ($folders as $folder) {
            $this->addFilesFromFileSystem($root, Path::join($relativePath, $folder));
        }
    }


    /** @return list<string> */
    public function files(): array
    {
        $index = 0;
        return \iterator_to_array(
            ($browseFolder = static function(array $tree, string $relativePath) use (&$browseFolder, &$index): \Generator {
                foreach ($tree as $name => $value) {
                    if (\is_array($value)) {
                        yield from $browseFolder($value, Path::join($relativePath, $name));
                    } else {
                        yield $index++ => Path::join($relativePath, $name);
                    }
                }
            })($this->tree, '')
        );
    }

    /** @return list<string> */
    public function emptyFolders(): array
    {
        $index = 0;
        return \iterator_to_array(
            ($browseFolder = static function(array $tree, string $relativePath) use (&$browseFolder, &$index): \Generator {
                foreach ($tree as $name => $value) {
                    if (\is_array($value)) {
                        if (\count($value) === 0) {
                            yield $index++ => Path::join($relativePath, $name);
                        } else {
                            yield from $browseFolder($value, Path::join($relativePath, $name));
                        }
                    }
                }
            })($this->tree, '')
        );
    }

    public function subtract(self $other): self
    {
        $subtracted = [];
        ($subtract = static function(array $ref, array $sub, array &$out) use (&$subtract): void {
            foreach ($ref as $key => $value) {
                if (\array_key_exists($key, $sub)) {
                    if (\is_array($value) && \is_array($sub[$key])) {
                        $subtracted = [];
                        $subtract($value, $sub[$key], $subtracted);
                        if (\count($subtracted) > 0) {
                            $out[$key] = $subtracted;
                        }
                    } elseif ($value !== $sub[$key]) {
                        $out[$key] = $value;
                    }
                } else {
                    $out[$key] = $value;
                }
            }
        })($this->tree, $other->tree, $subtracted);

        return self::fromArrayDump($subtracted);
    }

    /**
     * @var FileTreeDump
     */
    private array $tree = [];

    /**
     * @param FileTreeDump $newTree
     */
    private function mergeTree(array $newTree): void
    {
        $this->tree = \array_merge_recursive($this->tree, $newTree);
    }
}
