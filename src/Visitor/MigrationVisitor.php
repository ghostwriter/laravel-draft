<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Visitor;

use Ghostwriter\Draft\Visitor\Traits\NodeVisitorTrait;
use PhpParser\NodeVisitor;

final class MigrationVisitor implements NodeVisitor
{
    use NodeVisitorTrait;
}