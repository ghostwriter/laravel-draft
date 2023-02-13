<?php

declare(strict_types=1);

namespace Ghostwriter\Draft;

use Illuminate\Support\Facades\Facade;

/**
 * @see Draft
 */
final class DraftFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Draft';
    }
}
