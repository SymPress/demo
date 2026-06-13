<?php

declare(strict_types=1);

namespace SymPress\Demo\Asset;

use SymPress\Assets\DependencyExtractionAwareAsset;
use SymPress\Assets\Loader\EncoreEntrypointsLoader;
use SymPress\Assets\Script;
use SymPress\Demo\Support\PluginAssetLocator;

/**
 * Registers Encore-built demo assets through SymPress Assets.
 */
final readonly class DemoAssetRegistrar
{
    private const string ENTRYPOINTS_FILE = 'assets/entrypoints.json';

    public function __construct(
        private PluginAssetLocator $assets,
    ) {
    }

    public function registerWithSymPressAssets(object $assetManager): void
    {
        if (
            !method_exists($assetManager, 'register')
            || !class_exists(EncoreEntrypointsLoader::class)
        ) {
            return;
        }

        $entrypoints = $this->assets->path(self::ENTRYPOINTS_FILE);

        if (!is_readable($entrypoints)) {
            return;
        }

        $assets = (new EncoreEntrypointsLoader())
            ->withDirectoryUrl($this->assets->url('assets/'))
            ->load($entrypoints);

        foreach ($assets as $asset) {
            if ($asset instanceof DependencyExtractionAwareAsset) {
                $asset->withPhpDependencyFiles();
            }

            if ($asset instanceof Script) {
                $asset->defer();
            }

            $assetManager->register($asset);
        }
    }
}
