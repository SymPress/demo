<?php

declare(strict_types=1);

namespace SymPress\Demo\Command;

use SymPress\Demo\Infrastructure\WordPress\BlockRegistrar;
use SymPress\Kernel\Attribute\AsHook;
use SymPress\Orm\EntityManager;

/**
 * WP-CLI runtime check for the generated WordPress installation.
 */
final readonly class RuntimeSmokeCommand
{
    #[AsHook('cli_init')]
    public function register(): void
    {
        if (!defined('WP_CLI') || !WP_CLI || !class_exists('WP_CLI')) {
            return;
        }

        \WP_CLI::add_command('sympress-demo:runtime-smoke', $this);
    }

    /**
     * Verifies that the generated WordPress runtime can load the demo surface.
     *
     * ## EXAMPLES
     *
     *     wp sympress-demo:runtime-smoke
     */
    /**
     * @param list<string> $args
     * @param array<string, mixed> $assocArgs
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        unset($args, $assocArgs);

        $this->assertClassExists(EntityManager::class, 'SymPress ORM is not available.');

        do_action('rest_api_init');

        $routes = rest_get_server()->get_routes();

        if (!array_key_exists('/sympress-demo/v1/notes', $routes)) {
            \WP_CLI::error('The SymPress Demo REST route is not registered.');
        }

        if (
            !class_exists(\WP_Block_Type_Registry::class)
            || !\WP_Block_Type_Registry::get_instance()->is_registered(BlockRegistrar::BLOCK_NAME)
        ) {
            \WP_CLI::error('The SymPress Demo notes block is not registered.');
        }

        $rendered = do_blocks('<!-- wp:sympress-demo/notes {"limit":1} /-->');

        if (!is_string($rendered) || !str_contains($rendered, 'sympress-demo-notes')) {
            \WP_CLI::error('The SymPress Demo notes block did not render the expected markup.');
        }

        \WP_CLI::line('REST route, block registration, render and ORM smoke checks passed.');
        \WP_CLI::success('SymPress Demo runtime smoke test passed.');
    }

    /**
     * @param class-string $className
     */
    private function assertClassExists(string $className, string $message): void
    {
        if (!class_exists($className)) {
            \WP_CLI::error($message);
        }
    }
}
