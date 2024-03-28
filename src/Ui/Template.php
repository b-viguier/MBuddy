<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

/**
 * @phpstan-type Replacement Component|SubTemplate|string|\Stringable|int|float|null
 */
class Template
{
    public static function createEmpty(): static
    {
        return new static('', []);
    }

    /**
     * @param Replacement|iterable<Replacement> ...$replacements
     */
    public static function create(
        string $pattern,
        Component|SubTemplate|string|\Stringable|int|float|null|iterable ...$replacements
    ): static {
        return self::_create($pattern, $replacements);
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
        return $this->components;
    }

    /**
     * @param array<Replacement> $replacements
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
     * @param array<Replacement|iterable<Replacement>> $replacements
     */
    private static function _create(
        string $pattern,
        array $replacements
    ): static {
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
                    $subReplace[$subKey] = "{{ $newSubKey }}";
                }
                $toReplace[$key] = self::replace($replacement->pattern(), $subReplace);
                $components = \array_merge($components, $subComponents);
            } elseif(\is_iterable($replacement)) {
                $keyReplacement = '';
                foreach($replacement as $subKey => $subReplacement) {
                    $subTemplate = self::_create("{{ $subKey }}", [$subKey => $subReplacement]);
                    $subTemplateReplacements = [];
                    foreach ($subTemplate->components() as $subSubKey => $subSubComponent) {
                        $newSubTemplateKey = "$key#$subSubKey";
                        $components[$newSubTemplateKey] = $subSubComponent;
                        $subTemplateReplacements[$subSubKey] = "{{ $newSubTemplateKey }}";
                    }
                    $keyReplacement .= self::replace($subTemplate->pattern(), $subTemplateReplacements);

                }
                $toReplace[$key] = $keyReplacement;
            } else {
                $toReplace[$key] = $replacement;
            }
        }

        return new static(
            self::replace($pattern, $toReplace),
            $components,
        );
    }

    /**
     * @param array<Component> $components
     */
    final private function __construct(
        private string $pattern,
        private array $components,
    ) {
    }
}
