<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

/**
 * Loads plugin translations like a normal WordPress plugin would.
 */
final readonly class LocalizationRegistrar
{
    public function __construct(
        private string $pluginBase,
    ) {
    }

    public function load(): void
    {
        if (!function_exists('load_plugin_textdomain') || !function_exists('plugin_basename')) {
            return;
        }

        load_plugin_textdomain(
            'sympress-demo',
            false,
            dirname(plugin_basename($this->pluginBase)) . '/languages',
        );
    }
}
