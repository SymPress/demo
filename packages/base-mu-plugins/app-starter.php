<?php

declare(strict_types=1);

/**
 * Plugin Name: SymPress Demo App Starter
 */

namespace SymPress\Demo\BaseMuPlugins\AppStarter;

use SymPress\Kernel\App;
use SymPress\Kernel\Kernel\SiteKernel;

if (!class_exists(App::class)) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Resolve the website root for both copied and symlinked MU plugin installs.
 */
function resolve_project_dir(string $startDir): string
{
    $dir = $startDir;

    while (true) {
        if (is_file($dir . '/composer.json') && is_dir($dir . '/config')) {
            return $dir;
        }

        $parent = dirname($dir);

        if ($parent === $dir) {
            return dirname($startDir, 2);
        }

        $dir = $parent;
    }
}

App::bootKernel(new SiteKernel(resolve_project_dir(__DIR__)));
