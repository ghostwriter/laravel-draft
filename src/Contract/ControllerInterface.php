<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

use Closure;
use Ghostwriter\Draft\Contract\Controller\ActionInterface;

interface ControllerInterface
{
    /**
     * @param string                        $name    eg. 'index,create,show'
     * @param Closure(ActionInterface):void $factory
     */
    public function action(string $name, Closure $factory): void;

    /**
     * @return iterable<string,ActionInterface>
     */
    public function actions(): iterable;

    public function getModel(): ModelInterface;

    /**
     * @param class-string<ModelInterface>|string $name
     */
    public function model(string $name): ModelInterface;

    /**
     * @return iterable<string,ModelInterface>
     */
    public function models(): iterable;

    public function user(): UserInterface;

    public function withUser(UserInterface $user): self;
}
