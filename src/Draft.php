<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Closure;
use Ghostwriter\Draft\Contract\ControllerInterface;
use Ghostwriter\Draft\Contract\DraftInterface;
use Ghostwriter\Draft\Contract\ModelInterface;
use Ghostwriter\Draft\Value\Controller;
use Ghostwriter\Draft\Value\Migration;
use Ghostwriter\Draft\Value\Model;
use Ghostwriter\Draft\Value\Router;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\ParserFactory;

use function base_path;

final class Draft implements DraftInterface
{
    /** @var array<string,Controller> */
    private array $controllers = [];

    /** @var array<string,bool> */
    private array $factories = [];

    /** @var array<string,Node> */
    private array $files = [];

    /** @var array<string,Migration> */
    private array $migrations = [];

    /** @var array<string,Model> */
    private array $models = [];

    /** @var array<string,bool> */
    private array $seeders = [];

    public function __construct(
        private Container $container,
        private Dispatcher $dispatcher,
    ) {
        $dispatcher->listen(
            '*',
            static fn (string $eventName, array $attribute): mixed => dump($eventName, $attribute)
        );

        # options
        # arguments
        # tokens
    }

    public function controller(Model $model, Closure $fn): void
    {
        $this->controllers[$model->name()] = $fn(
            $model,
            new Controller($model),
            new Router($this->dispatcher, $this->container)
        );
    }

    public function controllers(): array
    {
        return $this->controllers;
    }

    public function factories(): array
    {
        return $this->factories;
    }

    public function factory(Model ...$models): void
    {
        foreach ($models as $model) {
            $tableName = $model->getTable();
            if (! array_key_exists($tableName, $this->factories)) {
                $this->factories[$tableName] = true;
            }
        }
    }

    public function hasController(ControllerInterface $controller): bool
    {
        return array_key_exists($controller->getModel()->name(), $this->controllers);
    }

    public function hasFactory(ModelInterface $model): bool
    {
        return array_key_exists($model->getTable(), $this->factories);
    }

    public function hasMigration(ModelInterface $model): bool
    {
        return array_key_exists($model->getTable(), $this->migrations);
    }

    public function hasModel(ModelInterface $model): bool
    {
        return array_key_exists(self::modelName($model), $this->models);
    }

    public function hasSeeder(ModelInterface $model): bool
    {
        return array_key_exists($model->getTable(), $this->seeders);
    }

    public function migration(Model $model, Closure $fn): void
    {
        $tableName = $model->getTable();
        $this->migrations[$tableName] = $fn($model, new Migration($tableName));
    }

    public function migrations(): array
    {
        return $this->migrations;
    }

    public function model(string $modelName, Closure $fn): Model
    {
        $model = $fn(new Model($modelName));

        return $this->models[self::modelName($model)] = $model;
    }

    public function modelPath(): string
    {
        return base_path('app/Models');
    }

    public function models(): array
    {
        return $this->models;
    }

    public function parse(string $code, string $path): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        return $this->files[basename($path, '.php')] = $parser->parse($code) ?? [];
    }

    public function seeder(Model ...$models): void
    {
        foreach ($models as $model) {
            $tableName = $model->getTable();
            if (! array_key_exists($tableName, $this->seeders)) {
                $this->seeders[$tableName] = true;
                $this->factories[$tableName] = true;
            }
        }
    }

    public function seeders(): array
    {
        return $this->seeders;
    }

    private static function modelName(ModelInterface $model): string
    {
        return Str::of($model->getTable())->singular()->ucfirst()->toString();
    }
}
