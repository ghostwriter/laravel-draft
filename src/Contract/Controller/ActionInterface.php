<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract\Controller;

use Ghostwriter\Draft\Contract\ModelInterface;

interface ActionInterface
{
    public function name(): string;

    public function statement(StatementInterface $statement): self;

    /**
     * @return iterable<string,StatementInterface>
     */
    public function statements(): iterable;

    public function with(string $key, ModelInterface $model): self;

    /**
     * @param iterable<string,ModelInterface> $param
     */
    public function withMany(iterable $param): self;
}
