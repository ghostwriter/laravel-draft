<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

interface ModelInterface
{
    public function getTable(): string;

    public function name(): string;
}
