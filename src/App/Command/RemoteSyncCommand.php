<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Command;

use Bveing\MBuddy\DevTools\ApiClient;
use Bveing\MBuddy\DevTools\FileTree;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mbuddy:remote:sync',
    description: 'Synchronize files with iPad'
)]
class RemoteSyncCommand extends Command
{
    /**
     * @param array<string> $synchronizedFiles
     */
    public function __construct(
        private string $projectDir,
        private array $synchronizedFiles,
        private ApiClient $apiClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("⏳ Retrieving remote files");
        $remoteDump = $this->apiClient->getFileDump();

        $output->writeln('🧮 Computing differences with local files');
        $remoteFileTree = FileTree::fromArrayDump($remoteDump);
        $localFileTree = new FileTree();
        foreach ($this->synchronizedFiles as $relativePath) {
            $localFileTree->addFilesFromFileSystem($this->projectDir, $relativePath);
        }
        $filesToUpload = $localFileTree->subtract($remoteFileTree)->files();
        $remoteOrphanTree = $remoteFileTree->subtract($localFileTree);
        $filesToDelete = \array_merge(
            \array_diff($remoteOrphanTree->files(), $filesToUpload),
            $remoteOrphanTree->emptyFolders(),
        );

        if ($filesToDelete) {
            $output->writeln('🗑️Deleting remote files');
            ['deleted' => $deletedFiles, 'errors' => $errors] =
                $this->apiClient->deleteFiles(\array_values($filesToDelete));

            if ($deletedFiles) {
                foreach ($deletedFiles as $file) {
                    $output->writeln(" - ✅ Deleted $file");
                }
            }

            if ($errors) {
                foreach ($errors as $error) {
                    $output->writeln(" - ❌ $error");
                }
                return Command::FAILURE;
            }
        } else {
            $output->writeln('🗑️ ✅ No remote files to delete');
        }

        if ($filesToUpload) {
            $output->writeln('📤 Uploading files');
            foreach ($filesToUpload as $file) {
                $response = $this->apiClient->uploadFile($this->projectDir, $file);
                if (isset($response['error'])) {
                    $output->writeln(" - ❌ Failed to upload $file: {$response['error']}");
                    return Command::FAILURE;
                }
                $output->writeln(" - ✅ Uploaded $file");
            }
        } else {
            $output->writeln('📤 ✅ No files to upload');
        }

        return Command::SUCCESS;
    }


}
