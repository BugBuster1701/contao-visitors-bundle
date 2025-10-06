<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    //// Ignoring errors
    ->ignoreErrors([ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('bugbuster/contao-clienthints-request-bundle', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('monolog/monolog', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnExtension('ext-iconv', [ErrorType::SHADOW_DEPENDENCY]);