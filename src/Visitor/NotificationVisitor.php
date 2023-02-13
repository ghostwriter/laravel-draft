<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Visitor;

use Ghostwriter\Draft\Visitor\Traits\NodeVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor;

final class NotificationVisitor implements NodeVisitor
{
    use NodeVisitorTrait;

    private ?Identifier $name = null;

    private ?Class_ $node = null;

    public function enterNode(Node $node): null|Node|int
    {
        if (! $node instanceof Class_) {
            return self::dontTraverseChildren();
        }

        $this->node = $node;

        $nodeName = $node->name;
        if (null === $nodeName) {
            $node->name = new Node\Identifier('Untitled');
            return $node;
        }

        $this->name = $nodeName;

        return null;
    }
}
