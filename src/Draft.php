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
use Ghostwriter\Draft\Value\User;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;

use function base_path;

final class Draft extends NodeVisitorAbstract implements DraftInterface
{
    /** @var array<string,ControllerInterface> */
    private array $controllers = [];

    /** @var array<string,bool> */
    private array $factories = [];

    /** @var array<string, array<Stmt>> */
    private array $files = [];

    /** @var array<string,MigrationInterface> */
    private array $migrations = [];

    /** @var array<string,ModelInterface|UserInterface> */
    private array $models = [];

    /** @var array<string,bool> */
    private array $seeders = [];

    /** @noinspection ForgottenDebugOutputInspection */
    public function __construct(
        private readonly Container $container,
        private readonly Dispatcher $dispatcher,
        private readonly Parser $parser,
    ) {
        //        $dispatcher->listen(
        //            '*',
        //            static fn (string $eventName, array $attribute): mixed => dump($eventName, $attribute)
        //        );

        # options
        # arguments
        # tokens

        /** @var class-string|string $key */
        $key = config('draft.default.user');
        $user = $this->container->has($key) ?
            $this->container->get($key) :
            new class() extends IlluminateModel {
            };

        // Todo: set the user UserInterface::class in models array.
        $this->models[UserInterface::class] = new class($user) implements UserInterface {
            private ?MigrationInterface $migration = null;

            public function __construct(
                private IlluminateModel $model
            ) {
                //
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
                return $this->model->getQualifiedKeyName();
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

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
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

    /**
     * @return array<Stmt>
     */
    public function parse(string $code, string $path): array
    {
        return $this->files[$path] ??=
            $this->parser->parse($code) ?? [];
    }


//    /**
//     * @param array<Stmt> $models
//     * @return array<Node>
//     */
//    public function traverse(array $nodes): array
//    {
//        return $this->traverser->traverse($nodes);
//    }

    public function seeder(Model ...$models): void
    {
        foreach ($models as $model) {
            $tableName = $model->table();
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
        //        $this->container->get($this->userProviderModel());
        return $this->models[UserInterface::class]
            ??= new User($this->container->build($this->userProviderModel()));
        //    ?? throw new RuntimeException('User model is missing.');
    }

    private static function modelName(ModelInterface $model): string
    {
        return Str::of($model->table())->singular()->ucfirst()->toString();
    }

    private function userProviderModel(): string
    {
        /** @var Repository $config */
        $config = $this->container->get('config');

        /** @var string $guard */
        $guard = $config->get('auth.defaults.guard');

        /** @var string $provider */
        $provider = $config->get(sprintf('auth.guards.%s.provider', $guard));

        /** @return class-string<Model> $model */
        return $config->get(sprintf('auth.providers.%s.model', $provider));
    }
}
