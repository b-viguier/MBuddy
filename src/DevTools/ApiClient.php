<?php

declare(strict_types=1);

namespace Bveing\MBuddy\DevTools;

use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @phpstan-import-type FileTreeDump from FileTree
 */
class ApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $iPadHost,
        private int $port,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return FileTreeDump
     */
    public function getFileDump(): array
    {
        return $this->request('GET', 'dev_tools_dump')->toArray();
    }

    /**
     * @param array<string> $files
     * @return array{deleted: array<string>, errors: array<string>}
     */
    public function deleteFiles(array $files): array
    {
        $response = $this->request('DELETE', 'dev_tools_delete', [
            'json' => \array_values($files),
        ])->toArray();
        \assert(isset($response['deleted'], $response['errors']));

        return $response;
    }

    /**
     * @return array{status?: string, error?: string}
     */
    public function uploadFile(string $root, string $file): array
    {
        $formData = new FormDataPart([
            'relative_path' => $file,
            'file' => DataPart::fromPath(Path::join($root, $file)),
        ]);

        return $this->request('POST', 'dev_tools_upload', [
            'body' => $formData->bodyToIterable(),
            'headers' => $formData->getPreparedHeaders()->toArray(),
        ])->toArray();
    }

    public function start(): void
    {
        try {
            $this->request(
                'GET',
                'system_server_start',
                [
                    'headers' => ['MBUDDY_START_PHP_SERVER' => 'true'],
                    'timeout' => 2,
                ],
                self::META_PORT,
            )->getStatusCode();
        } catch(TimeoutException) {
            // Expected, as the server is running inside the same PHP process
        }
    }

    public function stop(): void
    {
        try {
            $this->request(
                'GET',
                'system_server_stop',
                ['query' => ['ajax' => 'true']],
            )->getStatusCode();
        } catch(TransportException) {
            // Expected, as the server is stopping
        }
    }

    private const META_PORT = 8080;

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $route, array $options = [], ?int $port = null): ResponseInterface
    {
        $path = $this->urlGenerator->generate($route, referenceType: UrlGeneratorInterface::RELATIVE_PATH);
        $port = $port ?? $this->port;

        try {
            return $this->httpClient->request(
                $method,
                "http://$this->iPadHost:$port/$path",
                $options,
            );
        } catch (HttpExceptionInterface $e) {
            throw new \RuntimeException(
                'Error communicating with iPad: ' . \var_export($e->getResponse()->getContent(false), true),
                previous: $e,
            );
        }
    }
}
