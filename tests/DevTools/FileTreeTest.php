<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\DevTools;

use Bveing\MBuddy\DevTools\FileTree;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type FileTreeDump from FileTree
 */
class FileTreeTest extends TestCase
{
    public function testListingAddedFiles(): void
    {
        $tree = new FileTree();
        $tree->addFilesWithHash('', [
            'file1.txt' => 'hash1',
            'file2.txt' => 'hash2',
        ]);
        $tree->addFilesWithHash('folder1', [
            'file3.txt' => 'hash3',
        ]);
        $tree->addFilesWithHash('folder1/subfolder1', [
            'file4.txt' => 'hash4',
        ]);
        $tree->addFilesWithHash('folder2', [
            'file5.txt' => 'hash5',
        ]);
        $tree->addFilesWithHash('empty_folder/empty_subfolder', []);

        self::assertSame(
            [
                'file1.txt',
                'file2.txt',
                'folder1/file3.txt',
                'folder1/subfolder1/file4.txt',
                'folder2/file5.txt',
            ],
            $tree->files(),
        );
        self::assertSame(
            [
                'empty_folder/empty_subfolder',
            ],
            $tree->emptyFolders(),
        );

        self::assertSame(
            [
                'file1.txt' => 'hash1',
                'file2.txt' => 'hash2',
                'folder1' => [
                    'file3.txt' => 'hash3',
                    'subfolder1' => [
                        'file4.txt' => 'hash4',
                    ],
                ],
                'folder2' => [
                    'file5.txt' => 'hash5',
                ],
                'empty_folder' => [
                    'empty_subfolder' => [],
                ],
            ],
            $tree->dumpToArray(),
        );
    }

    public function testDumpWorkflow(): void
    {
        $srcTree = new FileTree();
        $srcTree->addFilesWithHash('', [
            'file1.txt' => 'hash1',
            'file2.txt' => 'hash2',
        ]);
        $srcTree->addFilesWithHash('folder1', [
            'file3.txt' => 'hash3',
        ]);
        $srcTree->addFilesWithHash('folder1/subfolder1', [
            'file4.txt' => 'hash4',
        ]);
        $srcTree->addFilesWithHash('folder2', [
            'file5.txt' => 'hash5',
        ]);
        $srcTree->addFilesWithHash('empty_folder/empty_subfolder', []);

        $dstTree = FileTree::fromArrayDump($srcTree->dumpToArray());
        self::assertSame(
            $srcTree->files(),
            $dstTree->files(),
        );
        self::assertSame(
            $srcTree->dumpToArray(),
            $dstTree->dumpToArray(),
        );
    }

    /**
     * @dataProvider subtractProvider
     * @param FileTreeDump $ref
     * @param FileTreeDump $other
     * @param FileTreeDump $expected
     */
    public function testSubtract(array $ref, array $other, array $expected): void
    {
        $refTree = FileTree::fromArrayDump($ref);
        $otherTree = FileTree::fromArrayDump($other);
        $resultTree = $refTree->subtract($otherTree);
        self::assertSame($expected, $resultTree->dumpToArray());
    }

    /**
     * @return iterable<string, array{ref: FileTreeDump, other: FileTreeDump, expected: FileTreeDump}>
     */
    public static function subtractProvider(): iterable
    {
        yield 'empty trees' => [
            'ref' => [],
            'other' => [],
            'expected' => [],
        ];

        yield 'no difference' => [
            'ref' => $ref = [
                'file1.txt' => 'hash1',
                'folder1' => [
                    'file2.txt' => 'hash2',
                ],
            ],
            'other' => $ref,
            'expected' => [],
        ];

        yield 'some differences' => [
            'ref' => [
                'file1.txt' => 'hash1',
                'missing_file.txt' => 'hash_missing',
                'different_hash.txt' => 'hash_different',
                'folder1' => [
                    'file1.txt' => 'hash1',
                    'missing_file.txt' => 'hash_missing',
                    'different_hash.txt' => 'hash_different',
                ],
                'missing_folder' => [
                    'file1.txt' => 'hash1',
                    'missing_file.txt' => 'hash_missing',
                    'different_hash.txt' => 'hash_different',
                ],
                'empty_folder' => ['empty_subfolder' => []],
                'missing_empty_folder' => ['empty_subfolder' => []],
            ],
            'other' => [
                'file1.txt' => 'hash1',
                'different_hash.txt' => 'not_the_same',
                'folder1' => [
                    'file1.txt' => 'hash1',
                    'different_hash.txt' => 'not_the_same',
                ],
                'empty_folder' => ['empty_subfolder' => []],
            ],
            'expected' => [
                'missing_file.txt' => 'hash_missing',
                'different_hash.txt' => 'hash_different',
                'folder1' => [
                    'missing_file.txt' => 'hash_missing',
                    'different_hash.txt' => 'hash_different',
                ],
                'missing_folder' => [
                    'file1.txt' => 'hash1',
                    'missing_file.txt' => 'hash_missing',
                    'different_hash.txt' => 'hash_different',
                ],
                'missing_empty_folder' => ['empty_subfolder' => []],
            ],
        ];
    }

    public function testAddFilesFromDir(): void
    {
        @\mkdir(__DIR__ . '/_files/empty_folder/empty_subfolder', recursive: true);
        $tree = new FileTree();
        $tree->addFilesFromFileSystem(__DIR__, '_files');
        self::assertSame(
            [
                '_files' => [
                    'file1.txt' => 'c147efcfc2d7ea666a9e4f5187b115c90903f0fc896a56df9a6ef5d8f3fc9f31',
                    'folder2' => [
                        'file4.txt' => '600456c60420b0c6ddfe3b8d50cb6e63af544fb26c5715ae58a601bcca9a055d',
                    ],
                    'empty_folder' => [
                        'empty_subfolder' => [],
                    ],
                    'folder1' => [
                        'file2.txt' => '3377870dfeaaa7adf79a374d2702a3fdb13e5e5ea0dd8aa95a802ad39044a92f',
                        'subfolder1' => [
                            'file3.txt' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                        ],
                    ],
                ],
            ],
            $tree->dumpToArray(),
        );
    }

    public function testAddIndividualFiles(): void
    {
        @\mkdir(__DIR__ . '/_files/empty_folder/empty_subfolder', recursive: true);
        $tree = new FileTree();
        $tree->addFilesFromFileSystem(__DIR__ .'/_files/', 'folder1');
        $tree->addFilesFromFileSystem(__DIR__ .'/_files/folder2', './');
        $tree->addFilesFromFileSystem(__DIR__ .'/_files', 'file1.txt');
        $tree->addFilesFromFileSystem(__DIR__ .'/_files', 'empty_folder');
        self::assertSame(
            [
                'folder1' => [
                    'file2.txt' => '3377870dfeaaa7adf79a374d2702a3fdb13e5e5ea0dd8aa95a802ad39044a92f',
                    'subfolder1' => [
                        'file3.txt' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                    ],
                ],
                'file4.txt' => '600456c60420b0c6ddfe3b8d50cb6e63af544fb26c5715ae58a601bcca9a055d',
                'file1.txt' => 'c147efcfc2d7ea666a9e4f5187b115c90903f0fc896a56df9a6ef5d8f3fc9f31',
                'empty_folder' => [
                    'empty_subfolder' => [],
                ],
            ],
            $tree->dumpToArray(),
        );
    }
}
