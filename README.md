# Draft

[![Compliance](https://github.com/ghostwriter/draft/actions/workflows/compliance.yml/badge.svg)](https://github.com/ghostwriter/draft/actions/workflows/compliance.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/draft?color=8892bf)](https://www.php.net/supported-versions)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/draft/coverage.svg)](https://shepherd.dev/github/ghostwriter/draft)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/draft)](https://packagist.org/packages/ghostwriter/draft)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/draft?color=blue)](https://packagist.org/packages/ghostwriter/draft)

A code generation tool for Laravel developers.

> **Warning**
>
> This project is not finished yet, work in progress.

This tool will write your draft ideas in to functional laravel code.

- Automatically load existing files and database to generate missing files. (e.g. Missing Tests)
- Fully written Tests for each file generated
- Jetstream with Livewire by v1.0

### Automated Features
- Models
- Factories
- Seeders
- Migrations
- Routes
- Unit Tests (90+ code coverage by v1.0)
- Feature Tests (90+ code coverage by v1.0)
- Controllers
  - FormRequest
  - Middleware
  - Gates & Policies
  - ResourceCollections
  - Statements
    - Mails
    - Notifications
    - Jobs
    - Events
    - RenderView


## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/draft --dev
```

## Usage

### Commands

create a draft file.

``` bash
php artisan draft:init
```

build the laravel application using the draft.php file

``` bash
php artisan draft:build
```

generate missing and or incomplete files along with writing feature/unit tests.

``` bash
php artisan draft:generate
```

trace an existing application to build a "draft.php" file.

``` bash
php artisan draft:trace
```

### Draft file: `draft.php`

```php
// Note: Some syntax below is currently pseudocode, I'm cleaning it up and building an API.
// ./draft.php
<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\Carbon;
use Ghostwriter\Draft\Contract\Controller\ActionInterface;
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

return static function (Draft $draft): void {
    //models:
    //Post:
    //      title: string:400
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
    // ===
    $user = $draft->model(User::class);

    $draft->makeModel('Post', static function (Draft $draft, Model $model): Model {
        $model->mergeCasts(['author_id'=> 'int', 'published_at'=> Carbon::class]);
        $model->fillable(['title', 'content', 'published_at']);
        $model->withMigration($post, static function (Draft $draft, Migration $table): Migration {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignIdFor(User::class, 'author_id');
            $table->timestamps();
            return $table;
        });

        $model->withController($post, static function (Draft $draft, Controller $controller): void {
            // $controller->resource(static fn (Action $action): Action => $action->render()->send());
            // $controller->resourceCollection(static fn (Action $action): Action => $action->render());

            $controller->invokable('create',static fn (Action $action): Action => $action->fire('CreatePost')->render());

            $controller->action('create',static fn (Action $action): Action => $action->fire('CreatePost')->render());
            $controller->action('store',static fn (Action $action): Action => $action->fire('SavePost')->render());
            $controller->action('show',static fn (Action $action): Action => $action->fire('GetPost')->render());

            $controller->action(
                    'view',
                    static fn (ActionInterface $action): Action => $action->query(
                        static fn (QueryStatement $queryStatement): QueryStatement => $queryStatement->with([
                            'query' => 'where',
                            'where' => ['title', 'content'],
                            'order' => 'published_at',
                            'limit' => 5,
                        ])
                    )
                    ->render();
            });
            return $controller;
        });

        return $model;
    });

    $post = $draft->model('Post');
    $draft->controller(
        $post,
        static function (Draft $draft, Controller $controller) use($user, $draft): Controller {
            $controller->model($post);
            $controller->action(
                    'show',
                    static fn (Action $action) use ($post): Action => $action->query(
                        'all',
                        static fn (QueryStatement $queryStatement): QueryStatement => $queryStatement->with([
                            'query' => 'where',
                            'where' => ['title', 'content'],
                            'order' => 'published_at',
                            'limit' => 5,
                        ])
                    )
                    ->render();
            });

            $controller->action('store', static function (Action $action) use ($post): Action {
                return $action->validate([])->flash()->with(['post' => $post]);
            });

            $controller->action(
                'index',
                static function (Action $action) use ($post): Action {
                    // query: where:title where:content order:published_at limit:5
                    $action->query(
                        'index',
                        static fn (QueryStatement $queryStatement): QueryStatement => $queryStatement->withMany([
                            'query' => 'where',
                            'where' => ['title', 'content'],
                            'order' => 'published_at',
                            'limit' => 5,
                        ])
                    )
                    ->render(static fn (RenderStatement $renderStatement): RenderStatement => $renderStatement->with([
                        'posts' => $post,
                        'users' => $controller->user()
                    ]));

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
        static function (Model $flight, Migration $table) use ($user): Migration {
            $table->id();
            $table->string('name');
            $table->string('airline');
            $table->foreignIdFor($user);
            $table->timestamps();

            return $table;
        }
    );
    // ===
    $draft->factory($user, $flight);
    $draft->seeder($user, $flight);
    // ===
    dd([
        //    array_map(static function (Blueprint $table): array {
        //        return array_map(static function (ColumnDefinition $column): mixed {
        //            return $column->getAttributes()['name'];
        //        }, $table->getColumns());
        //    }, $draft->migrations()),
        $draft->controllers(),
        $draft->factories(),
        $draft->migrations(),
        $draft->models(),
        $draft->seeders(),
    ]);
};
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

## Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` instead of using the issue tracker.

## Support

[[`Become a GitHub Sponsor`](https://github.com/sponsors/ghostwriter)]

## Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](https://github.com/ghostwriter/draft/contributors)

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
