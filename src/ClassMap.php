<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Ghostwriter\Draft\Contract\ControllerInterface;
use Ghostwriter\Draft\Contract\MigrationInterface;
use Ghostwriter\Draft\Contract\ModelInterface;
use Ghostwriter\Draft\Contract\UserInterface;
use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Stmt;

final class ClassMap
{
    /** @var array<string,ControllerInterface> */
    private array $controllers = [];

    /** @var array<string,bool> */
    private array $factories = [];

    /** @var array<string, array<Node|Stmt>> */
    private array $files = [];

    /** @var array<string,MigrationInterface> */
    private array $migrations = [];

    /** @var array<string,ModelInterface|UserInterface> */
    private array $models = [];

    /** @var array<string,bool> */
    private array $seeders = [];
    private array $map =  [
        'models' => [
            'model' => [
                'controllers' => [],
                'livewire-components' => [],
                'factories' => [],
                'policies' => [],
                'forms' => [],
                'migrations' => [],
            ],
            'controllers' => [
                'formRequests' => [],
            ],
            'livewire-components' => [
                'formRequests' => [],
            ],
        ],
        //        'createRequests' => [],
        //        'updateRequests' => [],
    ];

    public function __construct()
    {
    }

    public function addModel(array $info): void
    {
        $columns = [
            'models',
            'controllers',
            'factories',
            'policies',
            'migrations',
            'forms',
            //            'createRequests',
            //            'updateRequests',
        ];

        foreach ($columns as $column) {
            $key = Str::singular($column);
            //            $info[$key] ?? [];
            $this->map['models'][$column][$info[$key]['path']] = $info[$key];
        }
        //        $this->map['models'][$info['model']['name']] = ['path' => $info['model']['path'], 'exists' => $info['model']['exists']];
        //        $this->map['controllers'][$info['controller']['name']] = ['path' => $info['controller']['path'], 'exists' => $info['controller']['exists']];
        //        $this->map['factories'][$info['factory']['name']] = ['path' => $info['factory']['path'], 'exists' => $info['factory']['exists']];
        //        $this->map['policies'][$info['policy']['name']] = ['path' => $info['policy']['path'], 'exists' => $info['policy']['exists']];
        //        $this->map['migrations'][$info['migration']['name']] = ['path' => $info['migration']['path'], 'exists' => $info['migration']['exists']];
        //        $this->map['seeders'][$info['seeder']['name']] = ['path' => $info['seeder']['path'], 'exists' => $info['seeder']['exists']];

        //        $this->map[] = [
        //            'name' =>  $name,
        //            'rootNamespace' =>  $rootNamespace,
        //            'model' =>  $model,
        //            'modelClassPath' =>  $modelClassPath,
        //            'modelClassPathExists' =>  $modelClassPathExists,
        //            'controller' =>  $controller,
        //            'controllerClassPath' =>  $controllerClassPath,
        //            'controllerClassPathExists' =>  $controllerClassPathExists,
        //        ];
    }
}
