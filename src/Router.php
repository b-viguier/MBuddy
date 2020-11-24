<?php

namespace bviguier\MBuddy;

use bviguier\RtMidi\Message;

final class Router
{
    static public function fromHandlers(Handler ...$handlers): self
    {
        $instance = new self();
        foreach ($handlers as $handler) {
            if (null === $statusFilter = $handler->statusByte()) {
                $instance->defaultHandlers[] = $handler;
            } else {
                $instance->filteredHandlers[$statusFilter][] = $handler;
            }
        }

        return $instance;
    }

    public function handle(Message $message): void
    {
        if (null !== $filteredHandlers = $this->filteredHandlers[$firstByte = $message->byte(0)] ?? null) {
            for ($handler = reset($filteredHandlers);
                 $handler && $message;
                 $handler = next($filteredHandlers)) {
                $message = $handler->handle($message);
            }
        }

        for ($handler = reset($this->defaultHandlers);
             $handler && $message;
             $handler = next($this->defaultHandlers)) {
            $message = $handler->handle($message);
        }
    }

    /**
     * @var array<Handler>
     */
    private array $filteredHandlers = [];
    /**
     * @var array<Handler>
     */
    private array $defaultHandlers = [];

    private function __construct()
    {
    }
}
