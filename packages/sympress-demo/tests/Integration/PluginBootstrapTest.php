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

        $assetCompiler = $composer['extra']['sympress']['asset-compiler'];

        self::assertSame('build', $assetCompiler['script']['$mode']['$default']);
        self::assertSame('build:production', $assetCompiler['script']['$mode']['production']);
        self::assertSame('install', $assetCompiler['dependencies']);
        self::assertSame('npm', $assetCompiler['package-manager']);
        self::assertStringNotContainsString(
            'package-quality.php',
            json_encode($composer['scripts'], JSON_THROW_ON_ERROR),
        );
        self::assertContains(
            'if [ -x ../../vendor/bin/phpcs ]; then '
                . '../../vendor/bin/phpcs --standard=phpcs.xml.dist; '
                . 'else vendor/bin/phpcs --standard=phpcs.xml.dist; fi',
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
        self::assertSame('dev-main', $composer['require']['sympress/asset-compiler']);
        self::assertTrue($composer['config']['allow-plugins']['sympress/asset-compiler']);
        self::assertArrayNotHasKey('sympress.asset-compiler', $composer['extra']);

        $assetCompiler = $composer['extra']['sympress']['asset-compiler'];

        self::assertTrue($assetCompiler['auto-run']);
        self::assertSame('grouped', $assetCompiler['execution-strategy']);
        self::assertSame('npm', $assetCompiler['package-manager']);
        self::assertTrue($assetCompiler['packages']['sympress/demo-plugin']);
        self::assertArrayNotHasKey('compile-assets', $composer['scripts']);
        self::assertContains(
            "@composer compile-assets --mode production --ignore-lock='*'",
            $composer['scripts']['build:production'],
        );
        self::assertNotContains('@compile-assets', $composer['scripts']['post-install-cmd']);
        self::assertNotContains('@compile-assets', $composer['scripts']['post-update-cmd']);
        self::assertContains('sympress/kernel', $composer['extra']['sympress']['public_components_demonstrated']);
        self::assertContains(
            'sympress/asset-compiler',
            $composer['extra']['sympress']['public_components_demonstrated'],
        );
        self::assertContains('sympress/orm', $composer['extra']['sympress']['public_components_demonstrated']);
        self::assertContains('sympress/profiler', $composer['extra']['sympress']['public_components_demonstrated']);
        self::assertContains('bin/console', $composer['extra']['sympress']['starter_conventions']);
        self::assertContains('dev-ops/wpstarter.json', $composer['extra']['sympress']['starter_conventions']);
    }

    public function testRootComposerUsesPackagistForPublishedSymPressPackages(): void
    {
        $composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 4) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $repositoryTypes = [];
        $repositoryUrls = [];

        foreach ($composer['repositories'] as $repository) {
            $repositoryTypes[] = $repository['type'];
            $repositoryUrls[] = $repository['url'] ?? '';
        }

        self::assertContains('composer', $repositoryTypes);
        self::assertContains('path', $repositoryTypes);
        self::assertNotContains('vcs', $repositoryTypes);
        self::assertNotContains('https://github.com/SymPress/orm', $repositoryUrls);
        self::assertSame('dev-main', $composer['require']['sympress/orm']);
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
        self::assertSame('dev-main', $composer['require']['sympress/orm']);
        self::assertSame('build', $composer['extra']['sympress']['asset-compiler']['script']['$mode']['$default']);
        self::assertSame(
            'build:production',
            $composer['extra']['sympress']['asset-compiler']['script']['$mode']['production'],
        );
        self::assertArrayHasKey('@symfony/webpack-encore', $package['devDependencies']);
        self::assertArrayHasKey('typescript', $package['devDependencies']);
        self::assertSame('encore production', $package['scripts']['build:production']);
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
        self::assertStringContainsString(
            'SymPress\\Demo\\Infrastructure\\WordPress\\WordPressDemoNoteWriter',
            $services,
        );
        self::assertStringContainsString('SymPress\\Demo\\Support\\PluginAssetLocator', $services);
        self::assertStringContainsString('../src/Entity/DemoEventRecord.php', $services);
        self::assertStringContainsString('../src/Repository/DemoEventRecordRepository.php', $services);
        self::assertStringNotContainsString('kernel.hook', $services);
        self::assertStringNotContainsString('EventSystem::REGISTER_HOOK', $services);
        self::assertStringNotContainsString('db_migration_register', $services);
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

    public function testDemoProfilerCollectorSupportsCurrentAndLegacyCollectorMetadata(): void
    {
        $collector = (string) file_get_contents(
            dirname(__DIR__, 2) . '/src/Profiler/DemoProfilerCollector.php',
        );

        self::assertStringContainsString('function key(): string', $collector);
        self::assertStringContainsString('function label(): string', $collector);
        self::assertStringContainsString('function icon(): string', $collector);
        self::assertStringContainsString('function getKey(): string', $collector);
        self::assertStringContainsString('function getLabel(): string', $collector);
        self::assertStringContainsString('function getIcon(): string', $collector);
    }

    public function testDevelopmentProfilerConfigurationCollectsAndRegistersDemoCollector(): void
    {
        $rootDir = dirname(__DIR__, 4);
        $profilerConfig = (string) file_get_contents($rootDir . '/config/packages/development/profiler.yaml');
        $packageServices = (string) file_get_contents(dirname(__DIR__, 2) . '/config/services.yaml');
        $developmentServices = (string) file_get_contents(dirname(__DIR__, 2) . '/config/services_development.yaml');

        self::assertStringContainsString('profiler.collect: true', $profilerConfig);
        self::assertStringContainsString('SymPress\\Demo\\Profiler\\DemoProfilerCollector', $developmentServices);
        self::assertStringContainsString('autowire: true', $developmentServices);
        self::assertStringContainsString('profiler.collector', $developmentServices);
        self::assertStringContainsString("- '../src/Profiler/'", $packageServices);
    }

    public function testDemoPluginUsesHookEventAndLoggerAttributes(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $migrations = (string) file_get_contents($packageDir . '/src/Hook/DemoMigrations.php');
        $assets = (string) file_get_contents($packageDir . '/src/Asset/DemoAssetRegistrar.php');
        $subscriber = (string) file_get_contents($packageDir . '/src/EventSubscriber/LogRenderedNoteSubscriber.php');

        self::assertStringContainsString('use SymPress\\Kernel\\Attribute\\AsHook;', $migrations);
        self::assertStringContainsString("#[AsHook('db_migration_register', acceptedArgs: 1)]", $migrations);
        self::assertStringContainsString(
            "#[AsHook('db_migration_registered', priority: 20, acceptedArgs: 1)]",
            $migrations,
        );
        self::assertStringContainsString('#[AsHook(AssetManager::ACTION_SETUP, acceptedArgs: 1)]', $assets);
        self::assertStringContainsString('use SymPress\\EventDispatcher\\Attribute\\AsEventListener;', $subscriber);
        self::assertStringContainsString('#[AsEventListener(event: NoteRenderedEvent::class)]', $subscriber);
        self::assertStringContainsString('use Monolog\\Attribute\\WithMonologChannel;', $subscriber);
        self::assertStringContainsString("#[WithMonologChannel('sympress_demo')]", $subscriber);
        self::assertStringContainsString('$dispatcher->register($this)', $subscriber);
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

    public function testRuntimeSmokeCommandLivesInsideTheDemoCliSurface(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $command = (string) file_get_contents($packageDir . '/src/Command/RuntimeSmokeCommand.php');

        self::assertFileExists($packageDir . '/src/Command/RuntimeSmokeCommand.php');
        self::assertStringContainsString("#[AsHook('cli_init')]", $command);
        self::assertStringContainsString("add_command('sympress-demo:runtime-smoke'", $command);
        self::assertStringContainsString('rest_get_server()->get_routes()', $command);
        self::assertStringContainsString('WP_Block_Type_Registry::get_instance()->is_registered', $command);
        self::assertStringContainsString("do_blocks('<!-- wp:sympress-demo/notes", $command);
        self::assertStringContainsString('EntityManager::class', $command);
        self::assertFileDoesNotExist(dirname(__DIR__, 4) . '/bin/runtime-smoke.php');
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
        self::assertStringContainsString('EntityManager', $repository);
        self::assertStringContainsString('DemoEventRecordRepository', $repository);
        self::assertStringContainsString('extends AbstractMigration', $migration);
        self::assertStringContainsString('CREATE TABLE %ssympress_demo_events', $migration);
        self::assertStringContainsString('DROP TABLE IF EXISTS %ssympress_demo_events', $migration);
    }

    public function testDemoPluginMapsDemoEventsThroughSymPressOrm(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $entity = (string) file_get_contents($packageDir . '/src/Entity/DemoEventRecord.php');
        $repository = (string) file_get_contents($packageDir . '/src/Repository/DemoEventRecordRepository.php');
        $dashboard = (string) file_get_contents($packageDir . '/src/Admin/DemoDashboardPage.php');

        self::assertStringContainsString('use SymPress\\Orm\\Mapping\\Entity;', $entity);
        self::assertStringContainsString("#[Entity(table: 'sympress_demo_events'", $entity);
        self::assertStringContainsString('DemoEventRecordRepository::class', $entity);
        self::assertStringContainsString('#[GeneratedValue]', $entity);
        self::assertStringContainsString("#[Column(type: 'json', nullable: true)]", $entity);
        self::assertStringContainsString('extends Repository', $repository);
        self::assertStringContainsString('->save($record, flush: true)', $repository);
        self::assertStringContainsString("->select('COUNT(event.id)')", $repository);
        self::assertStringContainsString('SymPress\\Orm\\EntityManager', $dashboard);
        self::assertStringContainsString("'sympress/orm'", $dashboard);
        self::assertStringContainsString("'sympress/asset-compiler'", $dashboard);
        self::assertStringContainsString('starterConventions', $dashboard);
    }

    public function testDemoStaysAlignedWithStarterProjectConventions(): void
    {
        $rootDir = dirname(__DIR__, 4);
        $composer = json_decode(
            (string) file_get_contents($rootDir . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertFileExists($rootDir . '/bin/console');
        self::assertFileExists($rootDir . '/dev-ops/wpstarter.json');
        self::assertDirectoryExists($rootDir . '/packages/base-mu-plugins');
        self::assertFileExists($rootDir . '/.ddev/config.yaml');
        self::assertContains('bin/console', $composer['extra']['sympress']['starter_conventions']);
        self::assertContains('dev-ops/wpstarter.json', $composer['extra']['sympress']['starter_conventions']);
        self::assertContains('packages/base-mu-plugins', $composer['extra']['sympress']['starter_conventions']);
        self::assertContains('public/wp-content', $composer['extra']['sympress']['starter_conventions']);
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
        self::assertFileExists($packageDir . '/src/Entity/DemoEventRecord.php');
        self::assertFileExists($packageDir . '/src/Repository/DemoEventRecordRepository.php');
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

        self::assertSame('vendor/bin/wp sympress-demo:runtime-smoke', $composer['scripts']['qa:runtime']);
        self::assertContains(
            "@composer compile-assets --mode production --ignore-lock='*'",
            $composer['scripts']['build:production'],
        );
        self::assertContains('@qa:runtime', $composer['scripts']['build:production']);
        self::assertContains('@qa:runtime', $composer['scripts']['qa']);
    }
}
