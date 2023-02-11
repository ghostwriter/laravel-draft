<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use Generator;
use Ghostwriter\Draft\Draft;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node\Stmt;

final class FindControllers
{
    /**
     * @return Generator<string,array<Stmt>>
     * @throws FileNotFoundException
     */
    public function __invoke(Draft $draft, Filesystem $filesystem): Generator
    {
        foreach ($filesystem->files($draft->controllerPath()) as $controller) {
            $path = $controller->getRealPath();
            if ($path === false) {
                throw new FileNotFoundException();
            }
            yield $path => $draft->parse($filesystem->get($path), $controller->getFilename());
        }
    }
}
