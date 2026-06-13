<?php

declare(strict_types=1);

/**
 * Plugin Name: SymPress Demo VarDumper Integration
 * Description: Enables Symfony VarDumper helpers during frontend development.
 */

namespace SymPress\Demo\BaseMuPlugins\VarDumper;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('wp_get_environment_type') || wp_get_environment_type() !== 'development') {
    return;
}

if (!filter_var(getenv('SYMPRESS_DEMO_ENABLE_VARDUMPER'), FILTER_VALIDATE_BOOLEAN)) {
    return;
}

if (is_admin() || !class_exists(VarDumper::class)) {
    return;
}

VarDumper::setHandler(static function (mixed $value): void {
    $dumper = PHP_SAPI === 'cli' || (defined('WP_CLI') && WP_CLI)
        ? new CliDumper()
        : new HtmlDumper();

    $dumper->dump((new VarCloner())->cloneVar($value));
});
