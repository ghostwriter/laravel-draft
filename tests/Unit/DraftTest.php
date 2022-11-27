<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Tests\Unit;

use Ghostwriter\Draft\Draft;

/**
 * @coversDefaultClass \Ghostwriter\Draft\Draft
 *
 * @internal
 *
 * @small
 */
final class DraftTest extends AbstractTestCase
{
    /** @covers ::test */
    public function test(): void
    {
        self::assertTrue((new Draft())->test());
    }
}
