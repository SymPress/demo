<?php

declare(strict_types=1);

namespace SymPress\Demo\Support;

final readonly class PluginAssetLocator
{
    public function __construct(
        private string $pluginBase,
        private string $version,
    ) {
    }

    public function path(string $path): string
    {
        $base = $this->pluginBase;

        if (is_file($base)) {
            $base = dirname($base);
        }

        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    public function url(string $path): string
    {
        $base = $this->pluginBase;

        if (function_exists('plugin_dir_url') && is_file($base)) {
            $base = plugin_dir_url($base);
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    public function version(string $path): string
    {
        $file = $this->path($path);

        return is_file($file) ? (string) filemtime($file) : $this->version;
    }
}
