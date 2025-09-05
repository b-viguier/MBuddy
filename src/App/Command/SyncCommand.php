<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Command;

use Bveing\MBuddy\DevTools\FileTree;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'mbuddy:sync',
    description: 'Synchronize files with iPad'
)]
class SyncCommand extends Command
{
    /**
     * @param array<string> $synchronizedFiles
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $projectDir,
        private array $synchronizedFiles,
        private string $iPadHost,
        private int $port,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("⏳ Retrieving remote files");
        $remoteDump = $this->request('GET', 'dev_tools_dump');

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
                $this->request('DELETE', 'dev_tools_delete', [
                    'json' => \array_values($filesToDelete),
                ]);

            if ($deletedFiles) {
                \assert(\is_array($deletedFiles));
                foreach ($deletedFiles as $file) {
                    \assert(\is_string($file));
                    $output->writeln(" - ✅ Deleted $file");
                }
            }

            if ($errors) {
                \assert(\is_array($errors));
                foreach ($errors as $error) {
                    \assert(\is_string($error));
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
                $formData = new FormDataPart([
                    'relative_path' => $file,
                    'file' => DataPart::fromPath(Path::join($this->projectDir, $file)),
                ]);
                $response = $this->request('POST', 'dev_tools_upload', [
                    'body' => $formData->bodyToIterable(),
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                ]);
                if (isset($response['error'])) {
                    \assert(\is_string($response['error']));
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

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function request(string $method, string $route, array $options = []): array
    {
        $path = $this->urlGenerator->generate($route, referenceType: UrlGeneratorInterface::RELATIVE_PATH);

        try {
            return $this->httpClient->request(
                $method,
                "http://$this->iPadHost:$this->port/$path",
                $options,
            )->toArray();
        } catch (HttpExceptionInterface $e) {
            throw new \RuntimeException(
                'Error communicating with iPad: ' . \var_export($e->getResponse()->getContent(false), true),
                previous: $e,
            );
        }
    }
}
