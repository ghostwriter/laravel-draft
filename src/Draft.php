<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Closure;
use Ghostwriter\Draft\Contract\ControllerInterface;
use Ghostwriter\Draft\Contract\DraftInterface;
use Ghostwriter\Draft\Contract\MigrationInterface;
use Ghostwriter\Draft\Contract\ModelInterface;
use Ghostwriter\Draft\Contract\UserInterface;
use Ghostwriter\Draft\Exception\RuntimeException;
use Ghostwriter\Draft\Value\Controller;
use Ghostwriter\Draft\Value\Migration;
use Ghostwriter\Draft\Value\Model;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

use function base_path;

final class Draft implements DraftInterface
{
    /** @var array<string,ControllerInterface> */
    private array $controllers = [];

    /** @var array<string,bool> */
    private array $factories = [];

    /** @var array<string, array<Node|Stmt>> */
    private array $files = [];

    /** @var array<string,MigrationInterface> */
    private array $migrations = [];

    /** @var array<string,ModelInterface|UserInterface> */
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

        // Todo: set the user UserInterface::class in models array.
        $this->models[UserInterface::class] = new class(auth() ->user() ?? $this->container->get(
            config('draft.default.user')
        )) implements UserInterface {
            private ?MigrationInterface $migration = null;

            public function __construct(IlluminateModel $model)
            {
            }

            public function controller(): string
            {
                return '';
            }

            public function name(): string
            {
                return basename(config('draft.default.user'));
            }

            public function namespace(): string
            {
                return '';
            }

            public function table(): string
            {
                return Str::of($this->name())->plural()->lower()->toString();
            }

            public function migration(): MigrationInterface
            {
                return $this->migration ??= new Migration(new Model($this->name()));
            }

            public function withMigration(?Closure $factory = null): void
            {
                if ($factory instanceof Closure) {
                    $this->migration = $factory($this->migration());
                }
            }
        };
    }

    public function controller(ModelInterface $model, Closure $factory): ControllerInterface
    {
        $name = $model->table();
        if (array_key_exists($name, $this->controllers)) {
            return $this->controllers[$name];
        }

        throw new RuntimeException(sprintf('Controller "%s" dose not exists.', $name));
    }

    public function controllerPath(): string
    {
        return base_path('app/Http/Controllers');
    }

    public function controllers(): array
    {
        return $this->controllers;
    }

    public function factories(): array
    {
        return $this->factories;
    }

    public function factory(ModelInterface ...$models): void
    {
        foreach ($models as $model) {
            $table = $model->table();
            if (! array_key_exists($table, $this->factories)) {
                $this->factories[$table] = true;
            }
        }
    }

    public function hasController(ControllerInterface $controller): bool
    {
        return array_key_exists($controller->getModel()->name(), $this->controllers);
    }

    public function hasFactory(ModelInterface $model): bool
    {
        return array_key_exists($model->table(), $this->factories);
    }

    public function hasMigration(ModelInterface $model): bool
    {
        return array_key_exists($model->table(), $this->migrations);
    }

    public function hasModel(ModelInterface $model): bool
    {
        return array_key_exists($model->name(), $this->models);
    }

    public function hasSeeder(ModelInterface $model): bool
    {
        return array_key_exists($model->table(), $this->seeders);
    }

    public function makeController(ModelInterface $model, ?Closure $factory = null): void
    {
        $name = $model->name();
        if (array_key_exists($name, $this->controllers)) {
            throw new RuntimeException(sprintf('"%sController" already exists.', $name));
        }

        $factory ??= static fn (
            DraftInterface $draft,
            ControllerInterface $controller
        ): ControllerInterface => $controller;

        $controller = $factory($this, new Controller($model));
        if ($controller instanceof ControllerInterface) {
            $this->controllers[$name] = $controller->withUser($this->user());
            return;
        }

        throw new RuntimeException(sprintf('Failed to construct a "%sController".', $name));
    }

    public function makeModel(string $name, ?Closure $factory = null): void
    {
        $modelName = Str::of($name)->singular()->ucfirst()->toString();
        if (array_key_exists($modelName, $this->models)) {
            throw new RuntimeException(sprintf('Model "%s" already exists.', $name));
        }
        $factory ??= static fn (Draft $draft, Model $model): Model => $model;

        $this->models[$modelName] = $factory($this, new Model($name));
    }

    public function migrations(): array
    {
        return $this->migrations;
    }

    public function model(string $name): ModelInterface
    {
        $modelName = Str::of($name)->singular()->ucfirst()->toString();

        if (array_key_exists($modelName, $this->models)) {
            return $this->models[$modelName];
        }

        throw new RuntimeException(sprintf('Model "%s" does not exist.', $name));
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
        return $this->files[$path] ??=
            ((new ParserFactory())->create(ParserFactory::PREFER_PHP7))->parse($code) ??
            [];
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

    public function user(): UserInterface
    {
        return $this->models[UserInterface::class] ?? throw new RuntimeException('User model is missing.');
    }

    private static function modelName(ModelInterface $model): string
    {
        return Str::of($model->getTable())->singular()->ucfirst()->toString();
    }
}
