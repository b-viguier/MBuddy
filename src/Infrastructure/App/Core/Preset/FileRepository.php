<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\App\Core\Preset;

use Bveing\MBuddy\App\Core\Preset;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-type NormalizedPreset array{
 *     id: string,
 *     name: string,
 *     scoreTxt: string,
 * }
 */
class FileRepository implements Preset\Repository
{
    /**
     * @var array<string, Preset>
     */
    private array $presets = [];

    public function __construct(
        private string $filepath,
        private LoggerInterface $logger,
    )
    {
        $this->presets = $this->read();
    }

    public function get(Preset\Id $id): ?Preset
    {
        return $this->presets[$id->toString()] ?? null;
    }

    public function list(): iterable
    {
        yield from $this->presets;
    }

    public function add(Preset $preset): bool
    {
        $id = (string) $preset->id();
        if (isset($this->presets[$id])) {
            return false;
        }
        $this->presets[$id] = $preset;
        $this->write($this->presets);

        return true;
    }

    public function save(Preset $preset): bool
    {
        $id = (string) $preset->id();
        if (!isset($this->presets[$id])) {
            return false;
        }
        $this->presets[$id] = $preset;
        $this->write($this->presets);

        return true;
    }

    public function remove(Preset\Id $id): bool
    {
        $id = (string) $id;
        if (!isset($this->presets[$id])) {
            return false;
        }
        unset($this->presets[$id]);
        $this->write($this->presets);

        return true;
    }

    public function sort(Preset\Id ...$sortedIds): void
    {
        $previousPresets = $this->presets;
        $this->presets = [];
        foreach ($sortedIds as $id) {
            $stringId = $id->toString();
            if (isset($previousPresets[$stringId])) {
                $this->presets[$stringId] = $previousPresets[$stringId];
                unset($previousPresets[$stringId]);
            }
        }
        $this->presets = \array_merge($this->presets, $previousPresets);
        $this->write($this->presets);
    }

    public function surrounding(Preset\Id $id): array
    {
        $previous = null;
        for (
            $current = reset($this->presets);
            $current !== false;
            $previous = $current, $current = next($this->presets)
        ) {
            if ($current->id()->equals($id)) {
                $next = next($this->presets);
                return [
                    $previous,
                    $next !== false ? $next : null,
                ];
            }
        }

        return [null, null];
    }

    /**
     * @return array<string, Preset>
     */
    private function read(): array
    {
        if (!file_exists($this->filepath)) {
            return [];
        }

        if (false === $jsonContent = file_get_contents($this->filepath)) {
            $this->logger->error("Failed to read file content", ['file' => $this->filepath]);

            return [];
        }

        /** @var array<string, NormalizedPreset>|false $normalizedPresets */
        $normalizedPresets = json_decode($jsonContent, true, flags: JSON_THROW_ON_ERROR);
        if ($normalizedPresets === false) {
            $this->logger->error("Failed to json decode file content", ['file' => $this->filepath]);

            return [];
        }

        return \array_map(
            fn(array $data) => $this->denormalizePreset($data),
            $normalizedPresets,
        );
    }

    /**
     * @param array<string, Preset> $presets
     */
    private function write(array $presets): void
    {
        file_put_contents(
            $this->filepath,
            json_encode(
                \array_map(fn(Preset $preset) => $this->normalizePreset($preset), $presets),
                JSON_PRETTY_PRINT,
            )
        );
    }

    /**
     * @return NormalizedPreset
     */
    private function normalizePreset(Preset $preset): array
    {
        return [
            'id' => (string) $preset->id(),
            'name' => $preset->name(),
            'scoreTxt' => $preset->scoreTxt(),
        ];
    }

    /**
     * @param NormalizedPreset $data
     */
    private function denormalizePreset(array $data): Preset
    {
        return new Preset(
            id: Preset\Id::fromString($data['id']),
            name: $data['name'],
            scoreTxt: $data['scoreTxt'],
        );
    }
}
