<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use Generator;
use Ghostwriter\Draft\Draft;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node\Stmt;

final class FindModels
{
    /**
     * @throws FileNotFoundException
     *
     * @return Generator<string,array<Stmt>>
     *
     */
    public function __invoke(Draft $draft, Filesystem $filesystem): Generator
    {
        foreach ($filesystem->files($draft->modelPath()) as $controller) {
            $path = $controller->getRealPath();

            if (false === $path) {
                throw new FileNotFoundException();
            }

            yield $path => $draft->parse($filesystem->get($path), $controller->getFilename());
        }
    }
}
