<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Rendering;

use Bveing\MBuddy\Ui\Component;

class Template
{
    public static function createEmpty(): static
    {
        return new static('', []);
    }

    public static function create(string $pattern, Component|SubTemplate|string|\Stringable|int|float|null ...$replacements): static
    {
        $components = [];
        $toReplace = [];
        foreach($replacements as $key => $replacement) {
            if($replacement instanceof Component) {
                $components[$key] = $replacement;
            } elseif($replacement instanceof SubTemplate) {
                $subComponents = [];
                $subReplace = [];
                foreach($replacement->components() as $subKey => $subComponent) {
                    $newSubKey = "$key#$subKey";
                    $subComponents[$newSubKey] = $subComponent;
                    $subReplace[$subKey] = $newSubKey;
                }
                $toReplace[$key] = self::replace($replacement->pattern(), $subReplace);
                $components = \array_merge($components, $subComponents);
            } else {
                $toReplace[$key] = $replacement;
            }
        }

        return new static(
            self::replace($pattern, $toReplace),
            $components,
        );
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return array<string,Component>
     */
    public function components(): array
    {
        return $this->replacements;
    }

    /**
     * @param array<string|int,string|\Stringable|int|float|null> $replacements
     */
    public static function replace(string $pattern, array $replacements): string
    {
        return \str_replace(
            \array_map(
                fn($key) => "{{ $key }}",
                \array_keys($replacements),
            ),
            $replacements,
            $pattern,
        );
    }

    /**
     * @param array<Component> $replacements
     */
    final private function __construct(
        private string $pattern,
        private array $replacements,
    ) {
    }
}
