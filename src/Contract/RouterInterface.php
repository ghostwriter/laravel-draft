<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

interface RouterInterface
{
    public function user(): UserInterface;
}
