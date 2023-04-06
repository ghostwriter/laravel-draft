<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Command;

use Closure;
use Ghostwriter\Draft\Action\FindControllers;
use Ghostwriter\Draft\Action\FindModels;
use Ghostwriter\Draft\ClassMap;
use Ghostwriter\Draft\Draft;
use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node\Stmt;
use function floor;
use function log;

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

    public function convert(float|int $size): string
    {
        $i = floor(log($size, 1024));
        return sprintf(
            '%s %s',
            0 === $size ?
                0 :
                round($size / (1024 ** $i), 2),
            ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'][$i]
        );
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $classMap = new ClassMap();
        collect([
            'Tracing models' => fn (): bool =>
                [] !== dump(iterator_to_array(($this->findModels)($this->draft, $this->filesystem, $classMap))),
            'Tracing controllers' => fn (): bool =>
                [] !== dump(iterator_to_array(($this->findControllers)($this->draft, $this->filesystem, $classMap))),
        ])->each(
            fn (Closure $task, string $description): array =>
                $this->bench(fn () => $this->components->task($description, $task))
        );

        return self::SUCCESS;

        // Ask laravel where to find the model/controller/migrations/factories/seeders/formRequests/Notification dir
        //        dump([
        ////            iterator_to_array($models),
        ////            iterator_to_array($controllers),
        ////            $timeAndMemory_0,
        ////            $timeAndMemory_1,
        //            $testMem ?? 'testMem',
        //        ]);

        // Using PHP parser load each of the file paths/classNames in to a classmap

        // Using the classmap, determine which files are missing (based on a criteria/strategy)

        // User can enable/disable a "criteria/strategy" in `config/draft.php`

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
    }

    protected function bench(Closure $fn): array
    {
        $start = -hrtime(true);
        $memoryStart = -memory_get_usage(true);

        /** @var array<Stmt> $result */
        $result = $fn();

        $end = hrtime(true);
        $memoryEnd = memory_get_usage(true);

        $status = sprintf(
            'Time: %f secs. - Memory: %s',
            ($start + $end)/1E+9,
            self::convert($memoryEnd + $memoryStart)
        );

        $this->components->info($status);

        return [$result, $status];
    }

    protected function getStub(): void
    {
    }
}
