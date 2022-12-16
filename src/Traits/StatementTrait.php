<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Traits;

trait StatementTrait
{
    /**
     * @param array<string,string> $attributes
     */
    public function __construct(
        private string $name,
        private array $attributes = []
    ) {
    }

    public function attributes(): iterable
    {
        yield from $this->attributes;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function with(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function withAttribute(string $key, string $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function withMany(iterable $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }
}
