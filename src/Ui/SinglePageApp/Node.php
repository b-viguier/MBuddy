<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\SinglePageApp;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Id;
use Bveing\MBuddy\Ui\Template;

/**
 * @internal
 */
class Node
{
    public static function fromComponent(Component $component): self
    {
        $template = $component->template();
        $childrenNode = [];

        foreach($template->components() as $key => $component) {
            $childrenNode[$key] = self::fromComponent($component);
        }

        return new Node(
            $component,
            $component->version(),
            $template->pattern(),
            $childrenNode,
        );
    }

    public function render(): string
    {
        if(!$this->children) {
            return $this->pattern;
        }

        $replace = [];
        foreach($this->children as $key => $childNode) {
            $replace[$key] = $childNode->render();
        }

        return Template::replace($this->pattern, $replace);
    }

    public function id(): Id
    {
        return $this->component->id();
    }

    public function findComponent(Id $id): ?Component
    {
        if($this->id()->equals($id)) {
            return $this->component;
        }

        foreach($this->children as $childNode) {
            $result = $childNode->findComponent($id);
            if($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return iterable<Fragment>
     */
    public function update(): iterable
    {
        if ($this->component->version() === $this->version) {
            foreach ($this->children as $childNode) {
                yield from $childNode->update();
            }
            return;
        }

        $newTemplate = $this->component->template();
        $this->version = $this->component->version();
        $this->pattern = $newTemplate->pattern();

        $keptNodes = [];
        $newComp = [];
        foreach($newTemplate->components() as $key => $comp) {
            if(isset($this->children[$key]) && $this->children[$key]->component === $comp) {
                $keptNodes[$key] = $this->children[$key];
            } else {
                $newComp[$key] = $comp;
            }
        }

        // Update children previously here
        foreach($keptNodes as $node) {
            $node->update();
        }
        $this->children = $keptNodes;
        // Create new children
        foreach($newComp as $key => $comp) {
            $this->children[$key] = self::fromComponent($comp);
        }

        yield new Fragment(
            $this->id(),
            $this->render(),
        );
    }

    /**
     * @param array<Node> $children
     */
    private function __construct(
        private Component $component,
        private int $version,
        private string $pattern,
        private array $children,
    ) {
    }
}
