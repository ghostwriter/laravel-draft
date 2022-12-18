<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Visitor;

use Ghostwriter\Draft\Visitor\Traits\NodeVisitorTrait;
use PhpParser\NodeVisitor;

final class FormRequestVisitor implements NodeVisitor
{
    use NodeVisitorTrait;
}
