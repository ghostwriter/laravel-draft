<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Closure;
use Ghostwriter\Draft\Contract\Controller\ActionInterface;
use Ghostwriter\Draft\Contract\ControllerInterface;
use Ghostwriter\Draft\Contract\ModelInterface;
use Ghostwriter\Draft\Contract\UserInterface;
use Ghostwriter\Draft\Exception\RuntimeException;
use Ghostwriter\Draft\Value\Controller\Action;
use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

final class Controller extends IlluminateController implements ControllerInterface
{
    /** @var array<string,ActionInterface> */
    private array $actions = [];

    private bool $apiResource = false;

    private bool $apiResourceCollection = false;

    private bool $invokable = false;

    /** @var array<string,Middleware> */
    private array $middlewares = [];

    /** @var array<string,Model> */
    private array $models = [];

    private bool $resource = false;

    private ?UserInterface $user = null;

    public function __construct(
        private ModelInterface $model
    ) {
        //        'index' => 'viewAny',
        //            'create' => 'create',
        //            'store' => 'create',

        //            'show' => 'view',
        //            'edit' => 'update',
        //            'update' => 'update',
        //            'destroy' => 'delete',
        //        return $controller;
    }

    /**
     * @param Closure(ActionInterface):void $factory
     */
    public function action(string $name, ?Closure $factory = null): void
    {
        if (array_key_exists($name, $this->actions)) {
            throw new RuntimeException(sprintf('Action "%s" already exists.', $name));
        }

        $this->actions[$name] = new Action($name, $factory);
    }

    public function actions(): iterable
    {
        yield from $this->actions;
    }

    public function apiResource(): void
    {
        $this->apiResource = true;
    }

    public function apiResourceCollection(): void
    {
        $this->apiResourceCollection = true;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function invokable(): void
    {
        $this->invokable = true;
    }

    public function isApiResource(): bool
    {
        return $this->apiResource;
    }

    public function isApiResourceCollection(): bool
    {
        return $this->apiResourceCollection;
    }

    public function isInvokable(): bool
    {
        return $this->invokable;
    }

    public function isResource(): bool
    {
        return $this->resource;
    }

    public function model(string $name): ModelInterface
    {
        return $this->draft->model($name);
    }

    public function models(): iterable
    {
        yield from $this->models;
    }

    public function resource(): void
    {
        $this->resource = true;
    }

    public function user(): UserInterface
    {
        $user = $this->user;
        if ($user instanceof UserInterface) {
            return $user;
        }

        throw new RuntimeException('No user was provided.');
    }

//    public function route(Route $route): void
//    {
//        $this->router->controller($this::class);
//        //        Route::resource('photos', PhotoController::class);
//        //        Route::resources([
//        //            'photos' => PhotoController::class,
//        //            'posts' => PostController::class,
//        //        ]);
//    }
    public function withUser(UserInterface $user): self
    {
        $currentUser = $this->user;
        if (null !== $currentUser && $user === $currentUser) {
            return $this;
        }

        $copy = clone $this;
        $copy->user = $user;

        return $copy;
    }
}
