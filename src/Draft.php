<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

final class Draft
{
    /**
     * @var array<string,Model>
     */
    private array $models = [];

    /**
     * @var array<string,Blueprint>
     */
    private array $tables = [];

    /**
     * @param Closure(Model, Controller):Controller $param
     *
     */
    public function controller(Model $model, Closure $param): Controller
    {
        $controller = new class() extends Controller {
            use AuthorizesRequests;
            use DispatchesJobs;
            use ValidatesRequests;
        };

        $controller->middleware([
            new class($model, $param) {
                public function __construct(
                    private Model $model,
                    private Closure $param
                ) {}

                /**
                 * Handle an incoming request.
                 */
                public function handle(Request $request, Closure $next): mixed
                {
                    dd($request, $next);
                    return $next($request);
                }

                /**
                 * Handle tasks after the response has been sent to the browser.
                 */
                public function terminate(Request $request, Response $response): void
                {
                    unset($request, $response);
                }
            },
        ]);
        // (Request $request, Model $model)
        //        'index' => 'viewAny',
        //            'create' => 'create',
        //            'store' => 'create',

        //            'show' => 'view',
        //            'edit' => 'update',
        //            'update' => 'update',
        //            'destroy' => 'delete',

        var_dump([$controller]);

        return $controller;
    }

    public function getModels(): array
    {
        return $this->models;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param Closure(Model,Blueprint):void $param
     */
    public function migration(Model $model, Closure $param): void
    {
        $tableName = $model->getTable();

        $this->tables[$tableName] =
            new Blueprint($tableName, static fn (Blueprint $blueprint): mixed => $param($model, $blueprint));
    }

    /**
     * @param Closure(Model):Model $param
     */
    public function model(string $modelName, Closure $param): Model
    {
        $name = Str::of($modelName)->singular()->ucfirst()->toString();
        $tableName = Str::of($modelName)->plural()->lower()->toString();
        return $this->models[$name] = $param(new class() extends Model {
            protected $table = '';

            protected $dateFormat = 'U';

            public function getForeignKey(): string
            {
                return Str::of($this->table)->singular()->snake() . '_' . $this->getKeyName();
            }
        })->setTable($tableName);
    }
}
