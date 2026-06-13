const Encore = require('@symfony/webpack-encore');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

const isProduction = Encore.isProduction();

Encore
    .setOutputPath('assets/')
    .setPublicPath('./')
    .setManifestKeyPrefix('./')
    .addEntry('sympress-demo-backend', './resources/ts/admin.ts')
    .addEntry('sympress-demo-block-editor', './resources/ts/block-editor.ts')
    .addEntry('sympress-demo-frontend', './resources/ts/frontend.ts')
    .enableTypeScriptLoader()
    .enableSassLoader()
    .enableForkedTypeScriptTypesChecking((options) => {
        options.typescript = {
            ...(options.typescript || {}),
            memoryLimit: 4096,
        };
    })
    .enablePostCssLoader()
    .configureCssMinimizerPlugin((options) => {
        options.minimizerOptions = {
            preset: [
                'default',
                {
                    calc: false,
                },
            ],
        };
    })
    .enableSourceMaps(!isProduction)
    .disableSingleRuntimeChunk()
    .addPlugin(new DependencyExtractionWebpackPlugin())
    .splitEntryChunks();

Encore.cleanupOutputBeforeBuild();

module.exports = Encore.getWebpackConfig();
