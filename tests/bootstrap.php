<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2026 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

error_reporting(E_ALL);

require 'vendor/autoload.php';

// $include = fn ($file) => file_exists($file) ? include $file : false;

// Autoload the contao classes
$fixtureLoader = static function ($class): void {
    if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
        return;
    }
    if (str_contains($class, '\\') && 0 !== strncmp($class, 'Contao\\', 7)) {
        return;
    }
    if (0 === strncmp($class, 'Contao\\', 7)) {
        $class = substr($class, 7);
    }
    $namespaced = 'Contao\\'.$class;
    if (!class_exists($namespaced) && !interface_exists($namespaced) && !trait_exists($namespaced)) {
        return;
    }
    if (!class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)) {
        class_alias($namespaced, $class);
    }
};
spl_autoload_register($fixtureLoader, true, true);
