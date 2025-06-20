<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Async;

use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Server as SocketServer;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use function Amp\Http\Server\FormParser\parseForm;
use function Amp\Http\Server\Middleware\stack;

class Server
{
    public function __construct(
        private KernelInterface $kernel,
        private int $port,
        private LoggerInterface $logger,
        private string $uploadTmpDir,
    ) {
        \assert(\is_dir($uploadTmpDir) && \is_writable($uploadTmpDir), 'Upload temporary directory must be a writable directory');
    }

    public function isRunning(): bool
    {
        return $this->httpServer !== null;
    }

    public function stop(): void
    {
        if ($this->httpServer === null) {
            return;
        }

        $this->httpServer->stop(); //TODO: handle promise
        Loop::stop();
    }

    public function port(): int
    {
        return $this->port;
    }

    public function metaPort(): ?int
    {
        return $this->metaPort;
    }

    public function run(?SfRequest $metaRequest): void
    {
        \assert(!$this->isRunning(), 'Server is already running');
        \putenv('AMP_LOOP_DRIVER=' . NestableNativeDriver::class);
        $this->metaPort = $metaRequest?->getPort() === null ? null : \intval($metaRequest->getPort());
        try {
            Loop::run(function() {
                $requestHandler = \Closure::fromCallable([$this, 'handleRequest']);
                $options = new Options();
                if ($this->kernel->isDebug()) {
                    $options = $options->withDebugMode();
                    if (\function_exists('xdebug_connect_to_client')) {
                        $requestHandler = function(Request $request): \Generator {
                            \xdebug_connect_to_client();
                            return yield from $this->handleRequest($request);
                        };
                    }
                }
                $this->httpServer = new HttpServer(
                    [
                        SocketServer::listen("0.0.0.0:{$this->port}"),
                    ],
                    stack(
                        new CallableRequestHandler($requestHandler),
                    ),
                    $this->logger,
                    $options->withBodySizeLimit(1_048_576),
                );

                // Stop the server gracefully when SIGINT is received.
                // This is technically optional, but it is best to call Server::stop()
                if (\extension_loaded("pcntl")) {
                    \Amp\Loop::onSignal(\SIGINT, function(string $watcherId) {
                        \Amp\Loop::cancel($watcherId);
                        $this->stop();
                    });
                }

                \assert($this->httpServer !== null);
                yield $this->httpServer->start();

                $this->logger->info('HTTP server stopped');
            });
        } finally {
            $this->httpServer = null;
        }
    }

    private function handleRequest(Request $request): \Generator
    {
        try {
            $sfRequest = yield from $this->ampToSymfonyRequest($request);
            $sfResponse = $this->kernel->handle($sfRequest);

            try {
                $response = new Response(
                    $sfResponse->getStatusCode(),
                    $sfResponse->headers->all(),    // @phpstan-ignore-line
                );

                if ($sfResponse instanceof StreamedResponse
                    || $sfResponse instanceof BinaryFileResponse
                ) {
                    \ob_start(flags: \PHP_OUTPUT_HANDLER_REMOVABLE);
                    $sfResponse->sendContent();
                    $response->setBody(\ob_get_clean() ?: null);
                } else {
                    $response->setBody($sfResponse->getContent() ?: null);
                }

                return $response;
            } finally {
                if ($this->kernel instanceof TerminableInterface) {
                    $this->kernel->terminate($sfRequest, $sfResponse);
                    \gc_collect_cycles();
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error handling request: {message} (Code: {code}, File: {file}, Line: {line})',
                [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
            throw $e;
        }
    }

    private function ampToSymfonyRequest(Request $request): \Generator
    {
        [$content, $form] = yield Promise\all([
            $request->getBody()->buffer(),
            parseForm($request),
        ]);

        $client = $request->getClient()->getRemoteAddress();
        $server = [
            'REMOTE_ADDR' => $client->getHost(),
            'REMOTE_PORT' => $client->getPort(),
        ];

        $headers = $request->getHeaders();
        foreach ($headers as $name => $header) {
            $server['HTTP_' . \str_replace('-', '_', \strtoupper($name))] = $header[0];
        }

        $server += [
            'CONTENT_TYPE' => $contentType = $server['HTTP_CONTENT_TYPE'] ?? null,
            'CONTENT_LENGTH' => $server['HTTP_CONTENT_LENGTH'] ?? null,
        ];

        return SfRequest::create(
            (string)$request->getUri(),
            $request->getMethod(),
            $this->extractParameters($request->getMethod(), $request->getUri(), $content, (string) $contentType, $form),
            \array_map(fn(RequestCookie $cookie) => $cookie->getValue(), $request->getCookies()),
            $this->extractUploadedFiles($form),
            $server,
            $content
        );
    }

    /**
     * @return array<int|string,mixed>
     */
    private function extractParameters(string $method, UriInterface $uri, string $content, string $contentType, Form $form): array
    {
        $params = [];
        switch ($method) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                if (\str_starts_with($contentType, 'multipart/form-data')) {
                    return $this->extractMultipartFormData($form);
                }
                if ($contentType === 'application/x-www-form-urlencoded') {
                    \parse_str($content, $params);

                    return $params;
                }
        }
        \parse_str($uri->getQuery(), $params);

        return $params;
    }

    /**
     * @param Form $form
     * @return array<int|string,mixed>
     */
    private function extractMultipartFormData(Form $form): array
    {
        $params = [];
        foreach ($form->getValues() as $name => $values) {
            if (!empty($values)) {
                $params[$name] = \reset($values);
            }
        }

        return $params;
    }

    /**
     * @return array<string, UploadedFile>
     */
    private function extractUploadedFiles(Form $form): array
    {
        $files = [];
        foreach ($form->getFiles() as $name => $formFiles) {
            if (empty($formFiles)) {
                continue;
            }
            $file = \reset($formFiles);
            if (empty($file->getContents())) {
                continue;
            }
            if (false !== $fileLocation = \tempnam($this->uploadTmpDir, "uploaded_file")) {
                \file_put_contents($fileLocation, $file->getContents());

                $files[$name] = new UploadedFile(
                    $fileLocation,
                    $file->getName(),
                    $file->getMimeType(),
                    test: true,
                );
            } else {
                $this->logger->error(
                    'Failed to create temporary file for uploaded file: {name}',
                    ['name' => $file->getName()]
                );
            }
        }

        return $files;
    }

    private ?HttpServer $httpServer = null;
    private ?int $metaPort = null;
}
