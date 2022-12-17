<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

use Closure;

interface ModelInterface
{
    public function migration(): MigrationInterface;

    public function name(): string;

    public function namespace(): string;

    public function table(): string;

    /**
     * @param ?Closure(self,MigrationInterface):MigrationInterface $factory
     */
    public function withMigration(?Closure $factory = null): void;
}
