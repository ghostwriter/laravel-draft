<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Tests\Unit;

use Ghostwriter\Draft\Foo;

/**
 * @coversDefaultClass \Ghostwriter\Draft\Foo
 *
 * @internal
 *
 * @small
 */
final class FooTest extends AbstractTestCase
{
    /** @covers ::test */
    public function test(): void
    {
        self::assertTrue((new Foo())->test());
    }
}
