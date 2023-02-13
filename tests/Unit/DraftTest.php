<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Tests\Unit;

use Ghostwriter\Draft\Draft;
use Ghostwriter\Draft\DraftServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Draft::class)]
#[CoversClass(DraftServiceProvider::class)]
#[Small]
final class DraftTest extends \Orchestra\Testbench\TestCase
{
    private Container $container;

    private Draft $draft;

    protected function setUp(): void
    {
        $this->container = new Container();
        parent::setUp();

        $this->draft = $this->container->get(Draft::class);
    }

    public function testDraftControllersIsEmpty(): void
    {
        self::assertEmpty($this->draft->controllers());
    }

    public function testDraftFactoriesIsEmpty(): void
    {
        self::assertEmpty($this->draft->factories());
    }

    public function testDraftModelsIsNeverEmpty(): void
    {
        self::assertCount(1, $this->draft->models());
        self::assertNotEmpty($this->draft->models());
    }

    public function testDraftSeedersIsEmpty(): void
    {
        self::assertEmpty($this->draft->seeders());
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app->config->set('draft.debug', 'false');
        $app->config->set('draft.default', [
            'user' => '\App\Models\User',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [DraftServiceProvider::class];
    }
}
