<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Integration;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use SymPress\Demo\Entity\DemoEventRecord;
use SymPress\Demo\Support\PluginAssetLocator;
use SymPress\Demo\Support\TemplateRenderer;
use SymPress\Demo\SymPressDemoBundle;
use SymPress\Orm\Metadata\EntityClassRegistry;
use SymPress\Orm\Metadata\MetadataFactory;
use SymPress\Orm\OrmBundle;

final class BundleConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRelocatedComposerPackageUsesItsOwnAssetsViewsAndEntityMetadata(): void
    {
        $packageDir = dirname(__DIR__, 2);
        $installation = sys_get_temp_dir() . '/sympress-demo-plugin-' . bin2hex(random_bytes(8));
        $pluginDir = $installation . '/wordpress/wp-content/plugins/customer-demo';
        $filesystem = new Filesystem();

        try {
            $filesystem->mkdir([$pluginDir . '/src/Entity', $pluginDir . '/resources/views', $pluginDir . '/assets']);
            $filesystem->copy($packageDir . '/src/SymPressDemoBundle.php', $pluginDir . '/src/SymPressDemoBundle.php');
            $filesystem->copy($packageDir . '/src/Entity/DemoEventRecord.php', $pluginDir . '/src/Entity/DemoEventRecord.php');
            $filesystem->copy($packageDir . '/sympress-demo.php', $pluginDir . '/sympress-demo.php');
            $filesystem->copy($packageDir . '/composer.json', $pluginDir . '/composer.json');
            $filesystem->dumpFile($pluginDir . '/assets/entrypoints.json', '{"entrypoints":{}}');
            $filesystem->dumpFile(
                $pluginDir . '/resources/views/portability.php',
                '<?php echo $viewData["message"];',
            );

            require $pluginDir . '/src/SymPressDemoBundle.php';

            $bundle = new SymPressDemoBundle();
            $container = new ContainerBuilder();
            $container->setParameter('kernel.project_dir', $installation . '/another-project');
            $bundle->build($container);
            (new YamlFileLoader($container, new FileLocator($packageDir . '/config')))->load('services.yaml');
            $bundle->process($container);

            self::assertSame($pluginDir . '/sympress-demo.php', $container->getParameter('sympress_demo.plugin_file'));
            self::assertSame($pluginDir . '/resources/views', $container->getParameter('sympress_demo.view_path'));

            $pluginFile = $container->getParameterBag()->resolveValue(
                $container->getDefinition(PluginAssetLocator::class)->getArgument('$pluginBase'),
            );
            $viewPath = $container->getParameterBag()->resolveValue(
                $container->getDefinition(TemplateRenderer::class)->getArgument('$viewPath'),
            );

            self::assertIsString($pluginFile);
            self::assertIsString($viewPath);

            $assets = new PluginAssetLocator($pluginFile, '1.0.0');
            $renderer = new TemplateRenderer($viewPath);

            self::assertSame($pluginDir . '/assets/entrypoints.json', $assets->path('assets/entrypoints.json'));
            self::assertFileExists($assets->path('assets/entrypoints.json'));

            Functions\expect('plugin_dir_url')
                ->once()
                ->with($pluginFile)
                ->andReturn('https://example.test/wp-content/plugins/customer-demo/');

            self::assertSame(
                'https://example.test/wp-content/plugins/customer-demo/assets/entrypoints.json',
                $assets->url('assets/entrypoints.json'),
            );
            self::assertSame('Package-local view', $renderer->render('portability.php', ['message' => 'Package-local view']));

            $composer = json_decode(
                (string) file_get_contents($pluginDir . '/composer.json'),
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            self::assertSame('wordpress-plugin', $composer['type']);
            self::assertSame(SymPressDemoBundle::class, $composer['extra']['kernel']['bundle']);
            self::assertSame('sympress-demo/sympress-demo.php', $composer['extra']['kernel']['entry']);
            self::assertSame(['SymPress\\Demo\\' => 'src/'], $composer['autoload']['psr-4']);

            $entities = new EntityClassRegistry(new MetadataFactory(), [
                SymPressDemoBundle::class => ['path' => $pluginDir, 'package' => $composer['name']],
            ], classes: $container->getParameter('orm.entity_classes'));

            self::assertSame([DemoEventRecord::class], $entities->classes('sympress-demo-plugin'));
        } finally {
            $filesystem->remove($installation);
        }
    }

    public function testExplicitSitePathsRemainOverridable(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('sympress_demo.plugin_file', '/custom/plugins/demo.php');
        $container->setParameter('sympress_demo.view_path', '/custom/views');

        (new SymPressDemoBundle())->build($container);
        (new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config')))->load('services.yaml');

        self::assertSame('/custom/plugins/demo.php', $container->getParameter('sympress_demo.plugin_file'));
        self::assertSame('/custom/views', $container->getParameter('sympress_demo.view_path'));
    }

    public function testDemoEntityConfigurationPreservesOtherManagersAndAvoidsDuplicates(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('orm.entity_classes', [
            'another-plugin' => [DemoEventRecord::class],
        ]);
        $bundle = new SymPressDemoBundle();
        $bundle->process($container);
        $bundle->process($container);

        self::assertSame([
            'another-plugin' => [DemoEventRecord::class],
            'sympress-demo-plugin' => [DemoEventRecord::class],
        ], $container->getParameter('orm.entity_classes'));
    }

    public function testDemoEntityConfigurationPreservesDefaultManagerClassList(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('orm.entity_classes', [DemoEventRecord::class]);
        (new SymPressDemoBundle())->process($container);

        self::assertSame([
            'default' => [DemoEventRecord::class],
            'sympress-demo-plugin' => [DemoEventRecord::class],
        ], $container->getParameter('orm.entity_classes'));
    }

    public function testDemoEntityIsRegisteredBeforeTheOrmCatalogIsCompiled(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles_metadata', []);
        $container->setParameter('orm.entity_paths', []);
        $container->setParameter('orm.entity_classes', []);
        $container->setDefinition(MetadataFactory::class, new Definition(MetadataFactory::class));
        $container->setDefinition(EntityClassRegistry::class, (new Definition(EntityClassRegistry::class, [
            new Reference(MetadataFactory::class),
            '%kernel.bundles_metadata%',
            '%orm.entity_paths%',
            '%orm.entity_classes%',
        ]))->setPublic(true));

        (new OrmBundle())->build($container);
        (new SymPressDemoBundle())->build($container);
        $container->compile();
        $registry = $container->get(EntityClassRegistry::class);

        self::assertInstanceOf(EntityClassRegistry::class, $registry);
        self::assertSame([DemoEventRecord::class], $registry->classes('sympress-demo-plugin'));
    }
}
