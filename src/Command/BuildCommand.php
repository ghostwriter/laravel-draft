<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Command;

use Ghostwriter\Draft\Action\FindControllers;
use Ghostwriter\Draft\Action\FindModels;
use Ghostwriter\Draft\Draft;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class BuildCommand extends Command
{
    protected $description = 'Trace the full project for models';

    protected $signature = 'draft:build';

    public function __construct(
        private readonly Draft $draft,
        private readonly Filesystem $filesystem
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $models = iterator_to_array((new FindModels($this->draft, $this->filesystem))());
        $controllers = iterator_to_array((new FindControllers($this->draft, $this->filesystem))());

        dd([$models, $controllers]);
        //            $this->callSilently('queue:monitor', []);

        //        dd(app()->getNamespace());

        //        return self::SUCCESS;

        // dd($this->draft);
        //        $controllers = collect($this->filesystem->files(app()->basePath('app/http/controllers')))
        //            ->map->getPathname();
        //
        //        $formRequests = collect($this->filesystem->files(app()->basePath('app/http/requests')))
        //            ->map->getPathname();

        //        $models = $filesystem->files(app()->databasePath('app/models'));
        //        dump([
        //            'it works!',
        //            //            app()->basePath('app/models'),
        //            $models,
        //            $controllers,
        //            $formRequests,
        //            //            app()->configPath(),
        //            //            app()->databasePath(),
        //            //            app()->resourcePath(),
        //        ]);

        return self::SUCCESS;
    }
}
