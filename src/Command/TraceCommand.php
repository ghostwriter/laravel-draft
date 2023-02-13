<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Command;

use Closure;
use Ghostwriter\Draft\Action\FindControllers;
use Ghostwriter\Draft\Action\FindModels;
use Ghostwriter\Draft\Draft;
use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;

final class TraceCommand extends GeneratorCommand
{
    protected $description = 'Trace your project to generate a "draft.php" file.';

    protected $signature = 'draft:trace';

    public function __construct(
        private readonly Draft $draft,
        private readonly Filesystem $filesystem,
        private readonly FindModels $findModels,
        private readonly FindControllers $findControllers
    ) {
        parent::__construct($this->filesystem);
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        // Ask laravel where to find the model/controller/migrations/factories/seeders/formRequests/Notification dir

        [$models, $timeAndMemory_0] = $this->bench(fn () => ($this->findModels)($this->draft, $this->filesystem));

        [$controllers, $timeAndMemory_1] = $this->bench(
            fn () => ($this->findControllers)($this->draft, $this->filesystem)
        );

        //        $dump = [];
        //        [,$testMem] = $this->bench(function () use ($dump) {
        //            foreach (range(0, 100000) as $index){
        //                $dump[$index] = time();
        //            }
        //            return $dump;
        //        });
        dd([
            iterator_to_array($models),
            iterator_to_array($controllers),

            $timeAndMemory_0,
            $timeAndMemory_1,
            //    $testMem ?? null
        ]);
        die;
        //         =

        /** @var array<Class_> $classes */
        dd([
            $models,
            array_keys($models),
            //            array_values($models),
            //$this->draft->traverse($models[array_key_last($models)]),

            //            $this->qualifyClass($model),
            //            $this->qualifyModel($model),
            //$this->rootNamespace()
            //
            //            iterator_to_array($findModels($this->draft, $this->filesystem)),
            //            iterator_to_array($findControllers($this->draft, $this->filesystem)),
            //            //            $this->filesystem->files(app()->databasePath('app/models')),
            //            //        ??
            //            app()
            //                ->getNamespace(), // "app" or "custom if changed by user"
            //            app()
            //                ->getNamespace() . 'Actions',
            //            app()
            //                ->getNamespace() . 'Models',
            //            app()
            //                ->getNamespace() . 'Events',
            //            app()
            //                ->getNamespace() . 'Policies',
            //            app()
            //                ->getNamespace() . 'Views',
            //            app()
            //                ->getNamespace() . 'Http\\Controllers',
            //
            //            //            app()->basePath('Model'),
            //            app()->databasePath()
        ]);

        //        \Illuminate\Foundation\Application::class;

        // Using PHP parser load each of the file paths/classNames in to a classmap

        // Using the classmap, determine which files are missing (based on a criteria/strategy)

        // User can enable/disable a "criteria/strategy" in `config/draft.php`

        //
        //        $models =
        //        iterator_to_array((new FindModels($this->draft, $this->filesystem))());
        //        $controllers =
        //        iterator_to_array((new FindControllers($this->draft, $this->filesystem))());

        //            $this->callSilently('queue:monitor', []);

        //        dd(app()->getNamespace());
        //        dump([$models, $controllers]);

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

    protected function bench(Closure $fn): array
    {
        $start = -hrtime(true)/1e+6;
        $memoryStart = -memory_get_usage(true);

        /** @var array<Stmt> $result */
        $result = $fn();

        $end = hrtime(true)/1e+6;
        $memoryEnd = memory_get_usage(true);

        return [$result, sprintf('Time: %f - Memory: %s', $start + $end, $memoryEnd + $memoryStart)];
    }

    protected function getStub(): void
    {
    }
}
