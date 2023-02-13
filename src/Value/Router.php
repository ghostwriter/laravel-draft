<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Ghostwriter\Draft\Contract\RouterInterface;
use Illuminate\Routing\Router as IlluminateRouter;

final class Router extends IlluminateRouter implements RouterInterface
{
    public function user(): UserInterface
    {
        $user = $this->user;
        if ($user instanceof UserInterface) {
            return $user;
        }

        throw new RuntimeException('No user was provided.');
    }
}
