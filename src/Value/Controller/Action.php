<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value\Controller;

use Closure;
use Ghostwriter\Draft\Contract\Controller\ActionInterface;
use Ghostwriter\Draft\Contract\Controller\StatementInterface;
use Ghostwriter\Draft\Contract\ModelInterface;
use Ghostwriter\Draft\Exception\RuntimeException;
use Ghostwriter\Draft\Value\Controller\Statement\DispatchStatement;
use Ghostwriter\Draft\Value\Controller\Statement\FireStatement;
use Ghostwriter\Draft\Value\Controller\Statement\QueryStatement;
use Ghostwriter\Draft\Value\Controller\Statement\RenderStatement;
use Ghostwriter\Draft\Value\Controller\Statement\SendStatement;
use Ghostwriter\Draft\Value\Controller\Statement\SessionStatement;
use Ghostwriter\Draft\Value\Controller\Statement\ValidateStatement;

final class Action implements ActionInterface
{
    /** @var array<string,StatementInterface> */
    private array $statements = [];

    public function __construct(
        private string $name
    ) {
    }

    /**
     * @param Closure(DispatchStatement): DispatchStatement $fn
     */
    public function dispatchJob(string $jobName, Closure $fn): self
    {
        $this->statement($fn(new DispatchStatement($jobName)));

        return $this;
    }

    /**
     * @param Closure(FireStatement): FireStatement $fn
     */
    public function fire(string $eventName, Closure $fn): self
    {
        $this->statement($fn(new FireStatement($eventName)));

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param Closure(QueryStatement): QueryStatement $fn
     */
    public function query(string $query, Closure $fn): self
    {
        $this->statement($fn(new QueryStatement($query)));

        return $this;
    }

    /**
     * @param Closure(RenderStatement): RenderStatement $fn
     */
    public function render(string $view, Closure $fn): self
    {
        $this->statement($fn(new RenderStatement($view)));

        return $this;
    }

    /**
     * @param Closure(SendStatement): SendStatement $fn
     */
    public function send(string $mailable, Closure $fn): self
    {
        $this->statement($fn(new SendStatement($mailable)));

        return $this;
    }

    /**
     * @param Closure(SessionStatement): SessionStatement $fn
     */
    public function session(string $session, Closure $fn): self
    {
        $this->statement($fn(new SessionStatement($session)));

        return $this;
    }

    public function statement(StatementInterface $statement): ActionInterface
    {
        if (array_key_exists($statement::class, $this->statements)) {
            throw new RuntimeException(sprintf('Statement "%s" already exists.', $statement::class));
        }

        $this->statements[$statement::class] = $statement;

        return $this;
    }

    public function statements(): iterable
    {
        yield from $this->statements;
    }

    /**
     * @param Closure(ValidateStatement): ValidateStatement $fn
     */
    public function validate(string $invariant, Closure $fn): self
    {
        $this->statement($fn(new ValidateStatement($invariant)));

        return $this;
    }

    public function with(string $key, ModelInterface $value): ActionInterface
    {
        return $this;
    }

    public function withMany(iterable $param): ActionInterface
    {
        return $this;
    }
}
