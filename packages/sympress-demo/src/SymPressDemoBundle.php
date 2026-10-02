<?php

declare(strict_types=1);

namespace SymPress\Demo;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymPress\Demo\Entity\DemoEventRecord;
use SymPress\Kernel\Bundle\AbstractBundle;

final class SymPressDemoBundle extends AbstractBundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        // Register the demo entity before the ORM compiles its entity catalog.
        $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);

        $packageDir = dirname(__DIR__);

        if (!$container->hasParameter('sympress_demo.plugin_file')) {
            $container->setParameter('sympress_demo.plugin_file', $packageDir . '/sympress-demo.php');
        }

        if (!$container->hasParameter('sympress_demo.view_path')) {
            $container->setParameter('sympress_demo.view_path', $packageDir . '/resources/views');
        }
    }

    public function process(ContainerBuilder $container): void
    {
        /** @var list<class-string>|array<string, list<class-string>> $classes */
        $classes = $container->hasParameter('orm.entity_classes')
            ? $container->getParameter('orm.entity_classes')
            : [];

        if ($classes !== [] && array_is_list($classes)) {
            $classes = ['default' => $classes];
        }

        /** @var array<string, list<class-string>> $classes */
        $classes['sympress-demo-plugin'] = array_values(array_unique([
            ...($classes['sympress-demo-plugin'] ?? []),
            DemoEventRecord::class,
        ]));
        $container->setParameter('orm.entity_classes', $classes);
    }
}
