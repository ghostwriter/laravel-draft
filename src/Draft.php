<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

final class Draft
{
    /**
     * @var array<string,Model>
     */
    private array $models = [];

    /**
     * @var array<string,Blueprint>
     */
    private array $tables = [];

    /**
     * @param Closure(Model,Blueprint):Blueprint $param
     */
    public function migration(Model $model, Closure $param): void
    {
        $tableName = $model->getTable();

        $this->tables[$tableName] =
            new Blueprint($tableName, static fn (Blueprint $blueprint): mixed => $param($model, $blueprint));
    }

    /**
     * @param string $modelName
     * @param Closure(Model):Model $param
     *
     * @return Model
     */
    public function model(string $modelName, Closure $param): Model
    {
        $name = Str::of($modelName)->singular()->ucfirst()->toString();
        $tableName = Str::of($modelName)->plural()->lower()->toString();
        return $this->models[$name] = $param(new class() extends Model {
            protected $table = '';
            protected $dateFormat = 'U';
        })->setTable($tableName);
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function getModels(): array
    {
        return $this->models;
    }
}
