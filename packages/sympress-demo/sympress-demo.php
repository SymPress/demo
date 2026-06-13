<?php

declare(strict_types=1);

/*
 * Plugin Name:       SymPress Demo
 * Plugin URI:        https://github.com/SymPress/demo
 * Description:       Reference WordPress plugin demonstrating structured development with SymPress and Symfony components.
 * Version:           1.0.0
 * Author:            SymPress
 * Requires PHP:      8.5
 * Text Domain:       sympress-demo
 * License:           MIT
 */

namespace SymPress\Demo;

use SymPress\Kernel\App;
use function add_action;
use function esc_html__;

if (!defined('ABSPATH')) {
    return;
}

function autoload(): void
{
    if (class_exists(App::class) && class_exists(SymPressDemoBundle::class)) {
        return;
    }

    $autoloadCandidates = [
        __DIR__ . '/vendor/autoload.php',
        dirname(__DIR__, 4) . '/vendor/autoload.php',
    ];

    foreach ($autoloadCandidates as $autoload) {
        if (is_readable($autoload)) {
            require_once $autoload;

            return;
        }
    }
}

autoload();

if (!class_exists(App::class)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__(
            'SymPress Demo requires Composer dependencies. Run composer install in the project root.',
            'sympress-demo',
        );
        echo '</p></div>';
    });

    return;
}

add_action(
    'plugins_loaded',
    static function (): void {
        autoload();
    },
    0,
);
