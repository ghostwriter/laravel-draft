<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Traits;

trait StatementTrait
{
    private ?string $id = null;

    /**
     * @param array<string,string> $attributes
     */
    public function __construct(
        private string $name,
        private iterable $attributes = []
    ) {
    }

    public function getAttributes(): iterable
    {
        yield from $this->attributes;
    }

    public function getId(): string
    {
        return $this->id ??= spl_object_hash($this);
    }

    public function hasAttribute(string $key): bool
    {
        foreach ($this->attributes as $attributeKey => $attribute) {
            if ($key === $attributeKey) {
                return true;
            }
        }

        return false;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withAttribute(string $key, string $value): self
    {
        $copy = clone $this;
        $copy->attributes[$key] = $value;

        return $copy;
    }

    public function withoutAttribute(string ...$keys): self
    {
        $copy = clone $this;
        foreach ($keys as $key) {
            unset($copy->attributes[$key]);
        }

        return $copy;
    }
}
