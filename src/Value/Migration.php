<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Ghostwriter\Draft\Contract\MigrationInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

final class Migration extends Blueprint implements MigrationInterface
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

    public function name(): string
    {
        return $this->name;
    }
}
