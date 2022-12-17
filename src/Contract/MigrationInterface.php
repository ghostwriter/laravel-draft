<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

interface MigrationInterface
{
    public function getModel(): ModelInterface;
}
