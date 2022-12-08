<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

use Closure;
use Ghostwriter\Draft\Value\Controller;
use Ghostwriter\Draft\Value\Migration;
use Ghostwriter\Draft\Value\Model;
use Ghostwriter\Draft\Value\Router;

interface DraftInterface
{
    /**
     * @param Closure(Model,Controller,Router):Controller $fn
     */
    public function controller(Model $model, Closure $fn): void;

    public function hasController(ControllerInterface $controller): bool;

    public function hasFactory(ModelInterface $model): bool;

    public function hasMigration(ModelInterface $model): bool;

    public function hasModel(ModelInterface $model): bool;

    public function hasSeeder(ModelInterface $model): bool;

    /**
     * @param Closure(Model,Migration):Migration $fn
     */
    public function migration(Model $model, Closure $fn): void;

    /**
     * @param Closure(Model):Model $fn
     */
    public function model(string $modelName, Closure $fn): Model;
}
