<?php

declare(strict_types=1);

use Ghostwriter\Compliance\Configuration\ComplianceConfiguration;
use Ghostwriter\Compliance\Option\PhpVersion;
use Ghostwriter\Compliance\Option\Tool;

return static function (ComplianceConfiguration $complianceConfiguration): void {
    // $complianceConfiguration->phpVersion(PhpVersion::CURRENT_STABLE);
    $complianceConfiguration->phpVersion(PhpVersion::CURRENT_LATEST);
    $complianceConfiguration->skip([
        PhpVersion::PHP_82,
        Tool::CODECEPTION => [PhpVersion::PHP_80],
    ]);
};
