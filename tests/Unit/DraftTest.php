<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Tests\Unit;

use Ghostwriter\Draft\Draft;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Draft::class)]
#[Small]
final class DraftTest extends TestCase
{
    #[CoversNothing]
    public function test(): void
    {
        self::assertTrue(true);
    }
}
