<?php

declare(strict_types=1);

require_once '/Users/nathane/Desktop/GitHub/ghostwriter/draft/vendor/autoload.php';

use Carbon\Carbon;
use Ghostwriter\Draft\Draft;
use Ghostwriter\Draft\Value\Controller;
use Ghostwriter\Draft\Value\Controller\Action;
use Ghostwriter\Draft\Value\Controller\Statement\DispatchStatement;
use Ghostwriter\Draft\Value\Controller\Statement\FireStatement;
use Ghostwriter\Draft\Value\Controller\Statement\QueryStatement;
use Ghostwriter\Draft\Value\Controller\Statement\RenderStatement;
use Ghostwriter\Draft\Value\Controller\Statement\SessionStatement;
use Ghostwriter\Draft\Value\Controller\Statement\ValidateStatement;
use Ghostwriter\Draft\Value\Migration;
use Ghostwriter\Draft\Value\Model;
use Ghostwriter\Draft\Value\Router;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

return static function (Draft $draft): void {
    // ===
    //$draft = new Draft(new Dispatcher(), new Container());
    // ===
    //$user = $draft->model('User', static function (Model $user): Model {
    //    $user->fillable(['name', 'email', 'password', 'api_key', 'ssn', 'published_at']);
    //    $user->mergeCasts([
    //        'published_at'=> Carbon::class,
    //    ]);
    //    $user->makeHidden(['password', 'api_key', 'ssn']);
    //    return $user;
    //});
    //$draft->controller($user, static function (Model $model, Controller $controller, Router $router): Controller {
    //    //    dump([$model, $controller, $controller->getMiddleware()]);
    //    return $controller;
    //});
    //$draft->migration($user, static function (Model $model, Migration $table): Migration {
    //    $table->id();
    //    $table->string('name');
    //    $table->string('email');
    //    $table->string('password');
    //    $table->string('ssn');
    //    $table->timestamps();
    //
    //    return $table;
    //});
    // ===
    $flight = $draft->model('Flight', static function (Model $flight): Model {
        $flight->mergeCasts([
            'published_at'=> Carbon::class,
        ]);
        $flight->fillable(['name', 'airline', 'published_at']);
        return $flight;
    });

    $draft->controller(
        $flight,
        static function (Model $flight, Controller $controller, Router $router) use ($user): Controller {
            $controller->model($user);
            $controller->action(
                'index',
                static function (Action $action) use ($flight, $user): Action {
                    $action->dispatchJob(
                        'BookFlight',
                        static fn (DispatchStatement $dispatchStatement): DispatchStatement => $dispatchStatement
                    )->withMany([
                        'users' => $user,
                        'flights' => $flight,
                    ]);
                    $action->fire(
                        'FlightBooked',
                        static fn (FireStatement $fireStatement): FireStatement => $fireStatement
                    );
                    // query: where:title where:content order:published_at limit:5
                    $action->query(
                        'FlightBookedMail',
                        static fn (QueryStatement $queryStatement): QueryStatement => $queryStatement
                    )->withMany([
                        'query' => 'where',
                        'where' => ['title', 'content'],
                        'order' => 'published_at',
                        'limit' => 5,
                    ]);
                    $action->render(
                        'flight.index',
                        static fn (RenderStatement $renderStatement): RenderStatement => $renderStatement
                    )
                        ->with('flights', $flight);
                    $action->session(
                        'FlashMessage',
                        static fn (SessionStatement $sessionStatement): SessionStatement => $sessionStatement
                    );
                    $action->validate(
                        'Flight',
                        static fn (ValidateStatement $validateStatement): ValidateStatement => $validateStatement
                    );

                    //                $action->statement([
                    //                    'query' => 'all',
                    //                    'render' => 'post.index with:posts'
                    //                ]);

                    return $action;
                }
            );
            return $controller;
        }
    );

    $draft->migration(
        $flight,
        static function (Model $flight, Migration $migration) use ($user): Migration {
            $migration->id();
            $migration->string('name');
            $migration->string('airline');
            $migration->foreignIdFor($user);
            $migration->timestamps();

            return $migration;
        }
    );
    // ===
    $draft->factory($user, $flight);
    $draft->seeder($user, $flight);
    // ===
    dd([
        //    array_map(static function (Blueprint $migration): array {
        //        return array_map(static function (ColumnDefinition $column): mixed {
        //            return $column->getAttributes()['name'];
        //        }, $migration->getColumns());
        //    }, $draft->migrations()),
        $draft->controllers(),
        $draft->factories(),
        $draft->migrations(),
        $draft->models(),
        $draft->seeders(),
    ]);

    //    $draft->controller($controllers);
    //    $draft->seeders($seeders);
    //
    //    $post = $draft->model('Post', [
    //        'title' => 'string:400',
    //        'content' => 'longtext',
    //        'published_at' => 'nullable timestamp',
    //        'author_id' => 'id:user'
    //    ]);
    //
    //    $news = $draft->model('News', [
    //        'title' => 'string:400',
    //        'content' => 'longtext',
    //        'published_at' => 'nullable timestamp',
    //        'author_id' => $user
    //    ]);
    //
    //    $draft->controller('controllers', [
    //        'Post' => [
    //            'index' => [
    //                'query' => 'all',
    //                'render' => 'post.index with:posts'
    //            ],
    //            'store' => [
    //                'validate' => 'title, content, author_id',
    //                'save' => 'post',
    //                'send' => 'ReviewPost to:post.author.email with:post',
    //                'dispatch' => 'SyncMedia with:post',
    //                'fire' => 'NewPost with:post',
    //                'flash' => 'post.title',
    //                'redirect' => 'post.index'
    //            ]
    //        ]
    //    ]);
    //
    //        // Collections of Existing (maybe?)
    //        $models = $draft->models();
    //        $controllers = $draft->controllers();
    //        $seeders = $draft->seeders();
    //
    //        $models->merge([
    //
    //            $postModel = Model::generate('post', function (Model $model) {
    //                // Migration Specific
    //                $model->id();
    //                $model->string('title', 400);
    //                $model->longText('content');
    //                $model->timestamp('published_at')->nullable();
    //                $model->unsignedBigInteger('author_id')->casts('integer');
    //                $model->timestamps();
    //
    //                // Blueprint Specific
    //                // $model->unguard();
    //                $model->casts(['published_at' => \Carbon\Carbon::class]);
    //                $model->fillable(['title', 'content', 'published_at', 'author_id']);
    //            })
    //
    //        ]);
    //
    //        $controllers->merge([
    //            // $postResourceController = Controller::resource($postModel),
    //            // $postApiResourceController = Controller::apiResource($postModel),
    //            // $postApiResourceCollectionController = Controller::apiResourceCollection($postModel),
    //
    //            $postController = Controller::generate('post', function (Controller $controller) {
    //                // $invokeable = $controller->isInvokeable(); // bool - Single Action Controllers?
    //                // $model = $controller->model();
    //                // $controller->resource($model)
    //                // $controller->apiResource($model)
    //                // $controller->apiResourceCollection($model)
    //                // $controller->action($name, $callback(Action $action))
    //                $controller->action('index', function (Action $action) {
    //                    $controller = $action->controller();
    //                    $model = $controller->model();
    //
    //                    $action->statement(QueryStatement::class, 'all');
    //                    $action->statement(RenderStatement::class, 'post.index with:posts');
    //                    $action->statement(RenderStatement::class)->with('posts', $model);
    //                    $action->statement(RenderStatement::class)->with(['posts' => $model]);
    //                    $action->statement([
    //                        'query' => 'all',
    //                        'render' => 'post.index with:posts'
    //                    ]);
    //                });
    //
    //                Action::generate('index', function (Action $action) {
    //
    //                    Statement::generate([
    //                        'query' => 'all',
    //                        'render' => 'post.index with:posts'
    //                    ]),
    //
    //      }),
    //
    //      Action::generate('store', [
    //
    //          Statement::generate([
    //              'validate' => 'title, content, author_id',
    //              'save' => 'post',
    //              'send' => 'ReviewPost to:post.author.email with:post',
    //              'dispatch' => 'SyncMedia with:post',
    //              'fire' => 'NewPost with:post',
    //              'flash' => 'post.title',
    //              'redirect' => 'post.index',
    //          ]),
    //
    //      ])
    //
    //    }),
    //
    //        ]);
    //
    //        $draft->models($models);
    //};
    //models:
    //Post:
    //title: string:400
    //    content: longtext
    //    published_at: nullable timestamp
    //    author_id: id:user
    //
    //controllers:
    //  Post:
    //    index:
    //      query: all
    //      render: post.index with:posts
    //
    //    store:
    //      validate: title, content, author_id
    //      save: post
    //      send: ReviewPost to:post.author.email with:post
    //      dispatch: SyncMedia with:post
    //      fire: NewPost with:post
    //      flash: post.title
    //      redirect: post.index
};
