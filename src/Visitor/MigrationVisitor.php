<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Visitor;

use Ghostwriter\Draft\Visitor\Traits\NodeVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor;

final class MigrationVisitor implements NodeVisitor
{
    use NodeVisitorTrait;

    private ?Class_ $node = null;

    public function enterNode(Node $node): mixed
    {
        if (! $node instanceof Class_) {
            return null;
        }

        $this->node = $node;

        return null;
    }
}
