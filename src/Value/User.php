<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

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
        private UserModel $user
    ) {
    }

    public function name(): string
    {
        return $this->name ??= basename(self::class);
    }

    public function namespace(): string
    {
        return $this->namespace ??= (new ReflectionClass($this->user))->getNamespaceName();
    }

    public function table(): string
    {
        return $this->table ??= Str::of($this->name())->plural()->lower()->toString();
    }
}
