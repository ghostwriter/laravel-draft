<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Command;

use Ghostwriter\Draft\Draft;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use PhpParser\NodeDumper;
use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;

final class TraceCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trace the full project for models';

    protected $signature = 'draft:trace';

    public function __construct(
        private Draft $draft
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $filesystem): int
    {
        $models = collect($filesystem->files($this->draft->modelPath()))
            ->map(fn (
                SplFileInfo $fileInfo
            ): array => $this->draft->parse($filesystem->get($fileInfo->getRealPath()), $fileInfo->getFilename()));

        foreach ($models as $model) {
            dump([
                //                (new NodeDumper())->dump($model),
                (new Standard())->prettyPrintFile($model),
            ]);
        }

        //        dd($this->draft);

        die;
        $controllers = collect($filesystem->files(app()->basePath('app/http/controllers')))
            ->map->getPathname();

        $formRequests = collect($filesystem->files(app()->basePath('app/http/requests')))
            ->map->getPathname();

        //        $models = $filesystem->files(app()->databasePath('app/models'));
        dump([
            'it works!',
            //            app()->basePath('app/models'),
            $models,
            $controllers,
            $formRequests,
            //            app()->configPath(),
            //            app()->databasePath(),
            //            app()->resourcePath(),
        ]);

        return self::SUCCESS;
    }
}
