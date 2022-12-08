<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

use Closure;
use Ghostwriter\Draft\Contract\Controller\ActionInterface;

interface ControllerInterface
{
    /**
     * @param string                        $name  eg. 'index,create,show'
     * @param Closure(ActionInterface):void $param
     */
    public function action(string $name, Closure $param): void;

    /**
     * @return iterable<string,ActionInterface>
     */
    public function actions(): iterable;

    public function getModel(): ModelInterface;

    public function model(ModelInterface $model): void;

    /**
     * @return iterable<string,ModelInterface>
     */
    public function models(): iterable;
}
