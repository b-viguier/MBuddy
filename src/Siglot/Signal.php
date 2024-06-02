<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

class Signal
{
    /**
     * @param array<mixed> $args
     */
    public function __construct(private object $object, private string $method, private array $args)
    {}

    static public function auto(): self
    {
        $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $entry = $backtrace[1] ?? null;
        if ($entry === null) {
            throw new \Error("Attempt to create signal outside of a function");
        }

        if (!isset($entry['object'])) {
            throw new \Error("Attempt to create signal outside of a method");
        }

        return new self($entry['object'], $entry['function'], $entry['args'] ?? []);
    }

    public function object(): object
    {
        return $this->object; // Needed ?
    }

    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return mixed[]
     */
    public function args(): array
    {
        return $this->args;
    }
}
