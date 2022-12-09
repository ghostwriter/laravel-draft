<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Value;

use Ghostwriter\Draft\Contract\RouterInterface;
use Illuminate\Routing\Router as IlluminateRouter;

final class Router extends IlluminateRouter implements RouterInterface
{
}
