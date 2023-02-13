<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

use Closure;
use Ghostwriter\Draft\Value\Model;

interface DraftInterface
{
    public function hasController(ControllerInterface $controller): bool;

    public function hasFactory(ModelInterface $model): bool;

    public function hasMigration(ModelInterface $model): bool;

    public function hasModel(ModelInterface $model): bool;

    public function hasSeeder(ModelInterface $model): bool;

    /**
     * @param ?Closure(self,ControllerInterface):ControllerInterface $factory
     */
    public function makeController(ModelInterface $model, ?Closure $factory = null): void;

    /**
     * @param class-string|string                          $name
     * @param ?Closure(self,ModelInterface):ModelInterface $factory If the model exists keep this null
     */
    public function makeModel(string $name, ?Closure $factory = null): void;

    /**
     * @param class-string|string $name
     */
    public function model(string $name): ModelInterface;

    public function user(): UserInterface;

    /** @return array<Stmt> */
    public function parse(string $code, string $path): array;

    //$draft->controllers(),
    //$draft->factories(),
    //$draft->migrations(),
    //$draft->models(),
    //$draft->seeders(),
}
