<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Visitor;

use Ghostwriter\Draft\Visitor\Traits\NodeVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor;

final class ControllerVisitor implements NodeVisitor
{
    use NodeVisitorTrait;

    private ?Identifier $name = null;

    private ?Node $node = null;

    public function enterNode(Node $node): mixed
    {
        if (! $node instanceof Class_) {
            return null;
        }

        $this->node = $node;

        $nodeName = $node->name;
        if (null === $nodeName) {
            return null;
        }

        $this->name = $nodeName;

        return null;
    }
}
