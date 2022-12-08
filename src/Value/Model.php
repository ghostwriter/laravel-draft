<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Ghostwriter\Draft\Contract\ModelInterface;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Str;

final class Model extends IlluminateModel implements ModelInterface
{
    public function __construct(
        private string $name,
        array $attributes = []
    ) {
        $this->table = Str::of($this->name)->plural()->lower()->toString();
        parent::__construct($attributes);
    }

    public function getForeignKey(): string
    {
        /** @var string $this->table */
        return Str::of($this->table)
            ->singular()
            ->snake()
            ->append('_' . $this->getKeyName())
            ->toString();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function name(): string
    {
        return $this->name;
    }
}
