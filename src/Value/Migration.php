<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Ghostwriter\Draft\Contract\MigrationInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

final class Migration extends Blueprint implements MigrationInterface
{
    private ?string $foreignKey = null;

    public function __construct(
        private Model $model
    ) {
        parent::__construct($this->model->table());
    }

    public function getForeignKey(): string
    {
        /** @var string $this->table */
        return $this->foreignKey ??= Str::of($this->table)
            ->singular()
            ->snake()
            ->append('_' . $this->getKeyName())
            ->toString();
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
