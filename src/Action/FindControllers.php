<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use Ghostwriter\Draft\Draft;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use SplFileInfo;
use Traversable;

final class FindControllers
{
    public function __construct(
        private Draft $draft,
        private Filesystem $filesystem
    ) {
    }

    /**
     * @throws FileNotFoundException
     */
    public function __invoke(): iterable
    {
        foreach ($this->filesystem->files($this->draft->controllerPath()) as $controller) {
            $path = $controller->getRealPath();

            yield $path => $this->draft->parse($this->filesystem->get($path), $controller->getFilename());
        }
    }
}
