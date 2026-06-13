<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class PluginBootstrapTest extends TestCase
{
    public function testPluginFileContainsWordPressMetadataAndThinPackageBootstrap(): void
    {
        $pluginFile = dirname(__DIR__, 2) . '/sympress-demo.php';
        $contents = (string) file_get_contents($pluginFile);

        self::assertStringContainsString('Plugin Name:       SymPress Demo', $contents);
        self::assertStringContainsString('function autoload(): void', $contents);
        self::assertStringContainsString('plugins_loaded', $contents);
        self::assertStringNotContainsString('App::bootKernel', $contents);
        self::assertStringNotContainsString('SymPress\\Demo\\Plugin', $contents);
        self::assertStringNotContainsString('register_activation_hook', $contents);
        self::assertStringNotContainsString('register_post_type(', $contents);
        self::assertStringNotContainsString('add_shortcode(', $contents);
    }

    public function testBaseMuPluginBootsTheSiteKernelLikeAWebsiteProject(): void
    {
        $appStarter = dirname(__DIR__, 4) . '/packages/base-mu-plugins/app-starter.php';
        $contents = (string) file_get_contents($appStarter);

        self::assertFileExists($appStarter);
        self::assertStringContainsString('Plugin Name: SymPress Demo App Starter', $contents);
        self::assertStringContainsString('use SymPress\\Kernel\\Kernel\\SiteKernel;', $contents);
        self::assertStringContainsString('function resolve_project_dir(string $startDir): string', $contents);
        self::assertStringContainsString('App::bootKernel(new SiteKernel(resolve_project_dir(__DIR__)))', $contents);
    }

    public function testPackageComposerMetadataDeclaresKernelBundleEntryPoint(): void
    {
        $composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('sympress/demo-plugin', $composer['name']);
        self::assertSame('SymPress\\Demo\\SymPressDemoBundle', $composer['extra']['kernel']['bundle']);
        self::assertSame('sympress-demo', $composer['extra']['installer-name']);
        self::assertSame('build', $composer['extra']['sympress']['asset-compiler']['script']);
        self::assertSame('install', $composer['extra']['sympress']['asset-compiler']['dependencies']);
        self::assertSame('npm', $composer['extra']['sympress']['asset-compiler']['package-manager']);
        self::assertStringNotContainsString(
            'package-quality.php',
            json_encode($composer['scripts'], JSON_THROW_ON_ERROR),
        );
        self::assertContains(
            'if [ -x ../../vendor/bin/phpcs ]; then ../../vendor/bin/phpcs --standard=phpcs.xml.dist; else vendor/bin/phpcs --standard=phpcs.xml.dist; fi',
            $composer['scripts']['cs'],
        );
    }

    public function testRootComposerMetadataDeclaresWebsiteProjectAndPublicComponents(): void
    {
        $composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 4) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('sympress/demo', $composer['name']);
        self::assertSame('project', $composer['type']);
        self::assertSame('dev-main', $composer['require']['sympress/demo-base-mu-plugins']);
        self::assertTrue($composer['extra']['sympress.asset-compiler']['auto-run']);
        self::assertSame('npm', $composer['extra']['sympress.asset-compiler']['package-manager']);
        self::assertContains('cd packages/sympress-demo && npm run build', $composer['scripts']['compile-assets']);
        self::assertContains('@compile-assets', $composer['scripts']['post-install-cmd']);
        self::assertContains('@compile-assets', $composer['scripts']['post-update-cmd']);
        self::assertContains('sympress/kernel', $composer['extra']['sympress']['public_components_demonstrated']);
        self::assertContains('sympress/profiler', $composer['extra']['sympress']['public_components_demonstrated']);
    }

    public function testRootComposerUsesPackagistForSymPressPackages(): void
    {
        $composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 4) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $repositoryTypes = [];

        foreach ($composer['repositories'] as $repository) {
            $repositoryTypes[] = $repository['type'];
        }

        self::assertNotContains('vcs', $repositoryTypes);
        self::assertSame('dev-main', $composer['require-dev']['sympress/profiler']);
        self::assertArrayNotHasKey('sympress/profiler', $composer['require']);
    }

    public function testDemoPluginDefinesEncoreTypescriptAndProfilerIntegration(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $composer = json_decode(
            (string) file_get_contents($packageDir . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $package = json_decode(
            (string) file_get_contents($packageDir . '/package.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('dev-main', $composer['require-dev']['sympress/profiler']);
        self::assertArrayHasKey('@symfony/webpack-encore', $package['devDependencies']);
        self::assertArrayHasKey('typescript', $package['devDependencies']);
        self::assertFileExists($packageDir . '/webpack.config.js');
        self::assertFileExists($packageDir . '/tsconfig.json');
        self::assertFileExists($packageDir . '/src/Profiler/DemoProfilerCollector.php');
        self::assertFileExists($packageDir . '/config/services.yaml');
    }

    public function testDemoPluginUsesSymfonyStylePackageConfiguration(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $services = (string) file_get_contents($packageDir . '/config/services.yaml');
        $phpcs = (string) file_get_contents($packageDir . '/phpcs.xml.dist');

        self::assertFileExists($packageDir . '/config/services.yaml');
        self::assertStringContainsString('SymPress\\Demo\\:', $services);
        self::assertStringContainsString('SymPress\\Demo\\Repository\\NoteRepositoryInterface', $services);
        self::assertStringContainsString('SymPress\\Demo\\Repository\\WordPressNoteRepository', $services);
        self::assertStringContainsString('SymPress\\Demo\\Application\\Seed\\DemoNoteWriterInterface', $services);
        self::assertStringContainsString('SymPress\\Demo\\Infrastructure\\WordPress\\WordPressDemoNoteWriter', $services);
        self::assertStringContainsString('SymPress\\Demo\\Support\\PluginAssetLocator', $services);
        self::assertStringContainsString('kernel.hook', $services);
        self::assertStringContainsString('EventSystem::REGISTER_HOOK', $services);
        self::assertStringContainsString('db_migration_register', $services);
        self::assertStringContainsString('SymPress\\Demo\\Infrastructure\\WordPress\\BlockRegistrar', $services);
        self::assertStringNotContainsString('ShortcodeRegistrar', $services);
        self::assertFileDoesNotExist($packageDir . '/src/Plugin.php');
        self::assertFileDoesNotExist($packageDir . '/src/Domain');
        self::assertFileDoesNotExist($packageDir . '/src/Infrastructure/WordPress/ShortcodeRegistrar.php');
        self::assertFileDoesNotExist($packageDir . '/config/services.php');
        self::assertFileDoesNotExist($packageDir . '/config/wordpress.php');
        self::assertStringContainsString('SymPress-WordPress', $phpcs);
        self::assertStringContainsString('testVersion', $phpcs);
        self::assertStringContainsString('SymPress.Namespaces.Psr4', $phpcs);
    }

    public function testDemoProfilerCollectorUsesGetterStyleCollectorMetadata(): void
    {
        $collector = (string) file_get_contents(
            dirname(__DIR__, 2) . '/src/Profiler/DemoProfilerCollector.php',
        );

        self::assertStringContainsString('function getKey(): string', $collector);
        self::assertStringContainsString('function getLabel(): string', $collector);
        self::assertStringContainsString('function getIcon(): string', $collector);
        self::assertStringNotContainsString('function key(): string', $collector);
        self::assertStringNotContainsString('function label(): string', $collector);
        self::assertStringNotContainsString('function icon(): string', $collector);
    }

    public function testDemoSeedCommandDelegatesToApplicationServices(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $command = (string) file_get_contents($packageDir . '/src/Command/CreateDemoNotesCommand.php');

        self::assertStringContainsString('DemoNoteSeeder', $command);
        self::assertStringContainsString('CreateDemoNotesRequestFactory', $command);
        self::assertStringNotContainsString('wp_insert_post', $command);
        self::assertStringNotContainsString('wp_remote_get', $command);
        self::assertStringNotContainsString('wp_delete_post', $command);
        self::assertFileExists($packageDir . '/src/Application/Seed/DemoNoteSeeder.php');
        self::assertFileExists($packageDir . '/src/Application/Seed/DemoNoteWriterInterface.php');
        self::assertFileExists($packageDir . '/src/Infrastructure/WordPress/WordPressDemoNoteWriter.php');
        self::assertFileExists($packageDir . '/src/Infrastructure/WordPress/ZenQuotesQuoteProvider.php');
    }

    public function testDemoPluginLetsTheSymPressMigrationSystemOwnSchemaInstallation(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $pluginFile = (string) file_get_contents($packageDir . '/sympress-demo.php');
        $repository = (string) file_get_contents($packageDir . '/src/Infrastructure/Persistence/DemoEventRepository.php');
        $migration = (string) file_get_contents($packageDir . '/src/Migration/CreateDemoEventsTableMigration.php');

        self::assertStringNotContainsString('register_activation_hook', $pluginFile);
        self::assertStringNotContainsString('dbDelta', $repository);
        self::assertStringNotContainsString('function install', $repository);
        self::assertStringContainsString('extends AbstractMigration', $migration);
        self::assertStringContainsString('CREATE TABLE %ssympress_demo_events', $migration);
        self::assertStringContainsString('DROP TABLE IF EXISTS %ssympress_demo_events', $migration);
    }

    public function testRoadmapExamplesAreRepresentedInSourceTree(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $webpackConfig = (string) file_get_contents($packageDir . '/webpack.config.js');
        $block = json_decode(
            (string) file_get_contents($packageDir . '/resources/blocks/notes/block.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertFileExists($packageDir . '/src/Infrastructure/WordPress/RestApiRegistrar.php');
        self::assertFileExists($packageDir . '/src/Infrastructure/WordPress/BlockRegistrar.php');
        self::assertFileExists($packageDir . '/src/Infrastructure/WordPress/LocalizationRegistrar.php');
        self::assertFileExists($packageDir . '/src/Entity/Note.php');
        self::assertFileExists($packageDir . '/src/Entity/Topic.php');
        self::assertFileExists($packageDir . '/src/Repository/NoteRepositoryInterface.php');
        self::assertFileExists($packageDir . '/src/Repository/WordPressNoteRepository.php');
        self::assertFileExists($packageDir . '/src/Service/NoteService.php');
        self::assertFileExists($packageDir . '/src/Application/Query/NoteListQuery.php');
        self::assertFileExists($packageDir . '/src/Application/Query/NoteListQueryFactory.php');
        self::assertFileExists($packageDir . '/src/Presentation/NoteResourceFactory.php');
        self::assertFileExists($packageDir . '/src/Hook/DemoMigrations.php');
        self::assertFileExists($packageDir . '/src/Migration/CreateDemoEventsTableMigration.php');
        self::assertFileExists($packageDir . '/resources/ts/block-editor.ts');
        self::assertFileExists($packageDir . '/languages/sympress-demo.pot');
        self::assertFileExists($packageDir . '/languages/sympress-demo-de_DE.po');
        self::assertSame('sympress-demo/notes', $block['name']);
        self::assertStringContainsString('sympress-demo-block-editor', $webpackConfig);
    }

    public function testRootQualitySetupIncludesRuntimeSmokeTest(): void
    {
        $composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 4) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('vendor/bin/wp eval-file bin/runtime-smoke.php', $composer['scripts']['qa:runtime']);
        self::assertContains('@qa:runtime', $composer['scripts']['qa']);
    }
}
