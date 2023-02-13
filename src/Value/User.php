<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Closure;
use Ghostwriter\Draft\Contract\MigrationInterface;
use Ghostwriter\Draft\Contract\UserInterface;
use Illuminate\Contracts\Auth\Authenticatable as UserModel;
use Illuminate\Support\Str;
use ReflectionClass;

final class User implements UserInterface
{
    private ?string $name = null;

    private ?string $namespace = null;

    private ?string $table = null;

    public function __construct(
        private readonly UserModel $userModel
    ) {
    }

    public function migration(): MigrationInterface
    {
        return new Migration($this);
    }

    public function name(): string
    {
        return $this->name ??= basename(self::class);
    }

    public function namespace(): string
    {
        return $this->namespace ??= (new ReflectionClass($this->userModel))->getNamespaceName();
    }

    public function table(): string
    {
        return $this->table ??= Str::of($this->name())->plural()->lower()->toString();
    }

    public function withMigration(?Closure $factory = null): void
    {
    }
}
