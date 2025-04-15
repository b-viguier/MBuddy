<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\App\Core\Preset;

use Bveing\MBuddy\App\Core\Preset;

class FileRepository implements Preset\Repository
{
    private array $presets = [];

    public function __construct(
        private string $filepath
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

    private function read(): array
    {
        if (!file_exists($this->filepath)) {
            return [];
        }

        return \array_map(
            fn(array $data) => $this->denormalizePreset($data),
            json_decode(file_get_contents($this->filepath), true, flags: JSON_THROW_ON_ERROR),
        );
    }

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

    private function normalizePreset(Preset $preset): array
    {
        return [
            'id' => (string) $preset->id(),
            'name' => $preset->name(),
        ];
    }

    private function denormalizePreset(array $data): Preset
    {
        return new Preset(
            id: Preset\Id::fromString($data['id']),
            name: $data['name'],
        );
    }
}
