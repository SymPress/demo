<?php

declare(strict_types=1);

use SymPress\Demo\Infrastructure\WordPress\BlockRegistrar;

if (!defined('ABSPATH')) {
    fwrite(STDERR, 'WordPress is not loaded. Run this file through WP-CLI eval-file.' . PHP_EOL);

    exit(1);
}

if (!class_exists(BlockRegistrar::class)) {
    fwrite(STDERR, 'SymPress Demo plugin classes are not available.' . PHP_EOL);

    exit(1);
}

do_action('rest_api_init');

$routes = rest_get_server()->get_routes();

if (!array_key_exists('/sympress-demo/v1/notes', $routes)) {
    fwrite(STDERR, 'The SymPress Demo REST route is not registered.' . PHP_EOL);

    exit(1);
}

if (
    !class_exists(WP_Block_Type_Registry::class)
    || !WP_Block_Type_Registry::get_instance()->is_registered(BlockRegistrar::BLOCK_NAME)
) {
    fwrite(STDERR, 'The SymPress Demo notes block is not registered.' . PHP_EOL);

    exit(1);
}

$rendered = do_blocks('<!-- wp:sympress-demo/notes {"limit":1} /-->');

if (!is_string($rendered) || !str_contains($rendered, 'sympress-demo-notes')) {
    fwrite(STDERR, 'The SymPress Demo notes block did not render the expected markup.' . PHP_EOL);

    exit(1);
}

WP_CLI::line('REST route, block registration and render smoke checks passed.');
WP_CLI::success('SymPress Demo runtime smoke test passed.');
