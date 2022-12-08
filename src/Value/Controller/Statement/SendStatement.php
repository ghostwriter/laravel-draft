<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value\Controller\Statement;

use Ghostwriter\Draft\Contract\Controller\StatementInterface;
use Ghostwriter\Draft\Traits\StatementTrait;

final class SendStatement implements StatementInterface
{
    use StatementTrait;
}
