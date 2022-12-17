<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Closure;
use Ghostwriter\Draft\Contract\MigrationInterface;
use Ghostwriter\Draft\Contract\ModelInterface;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Str;

final class Model implements ModelInterface
{
    private ?MigrationInterface $migration = null;

    private ?IlluminateModel $model = null;

    private ?string $table = null;

    public function __construct(
        private string $name
    ) {
    }

    public function controller(): string
    {
        return '';
    }
//
//    public function getForeignKey(): string
//    {
//        /** @var self->table */
//        return Str::of($this->table)
//            ->singular()
//            ->snake()
//            ->append('_' . $this->getKeyName())
//            ->toString();
//    }

    public function migration(): MigrationInterface
    {
        return $this->migration ??= new Migration($this);
    }

    public function model(): IlluminateModel
    {
        return $this->model ??= new class() extends IlluminateModel {
        };
    }

    public function name(): string
    {
        return $this->name;
    }

    public function namespace(): string
    {
        return '';
    }

    public function table(): string
    {
        return $this->table ??= Str::of($this->name)->plural()->lower()->toString();
    }

    public function withMigration(?Closure $factory = null): void
    {
        if (null === $factory) {
            return;
        }

        $migration = $factory($this, $this->migration());
        if ($migration instanceof MigrationInterface) {
            $this->migration = $migration;
        }
    }
}
