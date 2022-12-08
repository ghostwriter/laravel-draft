<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value\Controller\Statement;

use Ghostwriter\Draft\Contract\Controller\StatementInterface;
use Ghostwriter\Draft\Traits\StatementTrait;

final class DispatchStatement implements StatementInterface
{
    use StatementTrait;

    /**
     * @param iterable<string,string> $data
     */
    public function __construct(
        private string $job,
        private iterable $data = []
    ) {
    }

    public function data(): iterable
    {
        yield from $this->data;
    }

    public function dispatchJob(string $string): self
    {
        return $this;
    }

    public function job(): string
    {
        return $this->job;
    }

    public function with(iterable $data = []): self
    {
        $this->data = $data;
        return $this;
    }
}
