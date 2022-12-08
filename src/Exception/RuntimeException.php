<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Exception;

use Ghostwriter\Draft\Contract\Exception\DraftExceptionInterface;
use RuntimeException as PhpRuntimeException;

final class RuntimeException extends PhpRuntimeException implements DraftExceptionInterface
{
}
