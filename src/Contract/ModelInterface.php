<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

interface ModelInterface
{
    public function name(): string;

    public function table(): string;
}
