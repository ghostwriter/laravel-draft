<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Command;

use Ghostwriter\Draft\ClassMap;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Livewire\Commands\MakeCommand;

final class NewCommand extends GeneratorCommand
{
    protected $description = 'Draft a new model.';

    protected $signature = 'draft:new {name}';

    public function classMap(string $name): array
    {
        // dd(app()->getNamespace());
        $model = $this->qualifyModel($name);
        $controller = $this->qualifyClass(sprintf('Http\Controllers\%sController', $name));
        $seeder = $this->qualifyClass(sprintf('Http\Controllers\%sController', $name));
        $factory = $this->qualifyClass(
            Str::of($name)->replaceFirst($this->rootNamespace(), '')
                ->start('Database\\Factories\\')
                ->finish('Factory')
        );
        $policy = $this->qualifyClass(sprintf('Policies\%sPolicy', $name));
        $migration = $this->qualifyClass(sprintf('Database\Migrations\create_%s_table', $name));

        $createFormRequest = $this->qualifyClass(sprintf('Http\Requests\Create%sRequest', $name));
        $updateFormRequest = $this->qualifyClass(sprintf('Http\Requests\Update%sRequest', $name));
        //        $this->call('make:seeder', [
        //            'name' => "{$seeder}Seeder",
        //        ]);
        //array_map(fn($i)=> $this->qualifyClass(sprintf('Http\Controllers\%sController', $name)),[])
        //        $info = ($name);

        return [
            'model' => $this->classMap1($model),
            'controller' => $this->classMap1($controller),
            'seeder' => $this->classMap1($seeder),
            'factory' => $this->classMap1($factory),
            'policy' => $this->classMap1($policy),
            'migration' => $this->classMap1($migration),
            'form' => [
                'path' => dirname($this->getPath($name)),
                $this->classMap1($createFormRequest),
                $this->classMap1($updateFormRequest),
            ],
            //            'createRequest' => $this->classMap1($createFormRequest),
            //            'updateRequest' => $this->classMap1($updateFormRequest),
            //            'formRequest' => $this->classMap1($formRequest),
            //            'exists' => $this->alreadyExists($name),
            //            'path' => $this->getPath($name),
        ];
    }

    /**
     * @return array{name: string, namespace: string, path: string, realpath: false|string, exists: bool}
     */
    public function classMap1(string $name): array
    {
        return [
            'name' => class_basename($name),
            'namespace' => $name,
            'path' => $this->getPath($name),
            'realpath' => realpath(dirname($this->getPath($name))),
            'exists' => $this->alreadyExists($name),
        ];
    }

    public function handle(): int
    {
        $name = Str::studly(class_basename($this->argument('name')));

        $info = $this->classMap($name);

        $rootNamespace = $this->rootNamespace();

        //        $namespaces = [
        //            'controllers' =>$rootNamespace.'\Http\Controllers',
        //            'events' =>$rootNamespace.'\Events',
        //            'mail' =>$rootNamespace.'\Mail',
        //        ];
        //
        //        $class = $this->qualifyClass($name);
        //        $controller = ;
        //        $path = $this->getPath($name);
        //        $model = $this->buildClass($name);

        $classMap = new ClassMap();
        $classMap->addModel($info);
        dd([
            $classMap,
            'app/Models/Ice.php',
            'database/factories/IceFactory.php',
            'database/migrations/2023_01_04_032916_create_ices_table.php',
            'app/Policies/IcePolicy.php',
        ]);

        //
        //        CLASS: app/Http/Livewire/Ice/Create.php
        //VIEW:  resources/views/livewire/ice/create.blade.php
        //TEST:  tests/Feature/Livewire/Ice/CreateTest.php
        // COMPONENT CREATED  🤙
        //
        //CLASS: app/Http/Livewire/Ice/Delete.php
        //VIEW:  resources/views/livewire/ice/delete.blade.php
        //TEST:  tests/Feature/Livewire/Ice/DeleteTest.php
        // COMPONENT CREATED  🤙
        //
        //CLASS: app/Http/Livewire/Ice/Index.php
        //VIEW:  resources/views/livewire/ice/index.blade.php
        //TEST:  tests/Feature/Livewire/Ice/IndexTest.php
        // COMPONENT CREATED  🤙
        //
        //CLASS: app/Http/Livewire/Ice/Show.php
        //VIEW:  resources/views/livewire/ice/show.blade.php
        //TEST:  tests/Feature/Livewire/Ice/ShowTest.php
        // COMPONENT CREATED  🤙
        //
        //CLASS: app/Http/Livewire/Ice/Store.php
        //VIEW:  resources/views/livewire/ice/store.blade.php
        //TEST:  tests/Feature/Livewire/Ice/StoreTest.php
        // COMPONENT CREATED  🤙
        //
        //CLASS: app/Http/Livewire/Ice/Update.php
        //VIEW:  resources/views/livewire/ice/update.blade.php
        //TEST:  tests/Feature/Livewire/Ice/UpdateTest.php
        // COMPONENT CREATED  🤙
        //
        //CLASS: app/Http/Livewire/ManageIces.php
        //VIEW:  resources/views/livewire/manage-ices.blade.php
        //TEST:  tests/Feature/Livewire/ManageIcesTest.php
        $this->info(sprintf('Drafting a new %s model.', $name));

        // Laravel
        // make:model name {--controller} {--resource} {--force} {--factory} {--migration} {--test}
        $this->call(ModelMakeCommand::class, [
            'name' => $name,
            '--factory' => true,
            '--force' => true,
            '--migration' => true,
            '--policy' => true,
            '--test' => true,
            // 'controller' => true, # using Livewire instead
            // 'resource' =>true, # using Livewire instead
            // '--seed' => true, # May not use it (Use the Default [DatabaseSeeder] to "Build a Story".)
        ]);

        // Livewire
        // livewire:make name {--force} {--inline} {--test} {--stub=}
        //      - Livewire allows us to use our own stubs, so create one for each "Action" component.
        // Todo: Admins & Owners or Role based.
        array_map(
            fn (string $file): int => $this->call(MakeCommand::class, [
                'name' => $file,
                '--test' => true,
                '--force' => true,
            ]),
            [
                $name . '.Create',
                $name . '.Delete',
                $name . '.Index',
                $name . '.Show',
                $name . '.Store',
                $name . '.Update',

                Str::of($name)
                    ->prepend('Manage')
                    ->pluralStudly()
                    ->toString(),
            ]
        );

        return self::SUCCESS;
    }

    protected function getStub()
    {
        //        return base_path('stubs');
    }
}
