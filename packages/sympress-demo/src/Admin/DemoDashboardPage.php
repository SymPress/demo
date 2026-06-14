<?php

declare(strict_types=1);

namespace SymPress\Demo\Admin;

use SymPress\Assets\AssetManager;
use SymPress\AssetCompiler\Composer\Plugin as AssetCompilerPlugin;
use SymPress\Demo\Application\Seed\DemoNoteWriterInterface;
use SymPress\Demo\Application\Seed\RemoteQuoteProviderInterface;
use SymPress\Demo\Entity\DemoEventRecord;
use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;
use SymPress\Demo\Infrastructure\Persistence\DemoEventRepository;
use SymPress\Demo\Infrastructure\WordPress\BlockRegistrar;
use SymPress\Demo\Infrastructure\WordPress\WordPressDemoNoteWriter;
use SymPress\Demo\Infrastructure\WordPress\ZenQuotesQuoteProvider;
use SymPress\Demo\Profiler\DemoProfilerCollector;
use SymPress\Demo\Repository\DemoEventRecordRepository;
use SymPress\Demo\Repository\NoteRepositoryInterface;
use SymPress\Demo\Repository\WordPressNoteRepository;
use SymPress\Demo\Support\PluginAssetLocator;
use SymPress\EventDispatcher\Application\EventSystem;
use SymPress\Kernel\App;
use SymPress\Kernel\Attribute\AsHook;
use SymPress\MonologBundle\MonologBundle;
use SymPress\Orm\EntityManager;
use SymPress\Profiler\ProfilerBundle;
use SymPress\WordPress\Migration\Application\MigrationSystem;
use SymPress\WpCliConsole\WpCliConsoleBundle;
use SymPressCS\SymPress\Helpers\Boundaries;

final readonly class DemoDashboardPage
{
    public function __construct(
        private DemoEventRepository $events,
    ) {
    }

    #[AsHook('admin_menu')]
    public function register(): void
    {
        add_menu_page(
            __('SymPress Demo', 'sympress-demo'),
            __('SymPress Demo', 'sympress-demo'),
            'manage_options',
            'sympress-demo',
            [$this, 'render'],
            'dashicons-welcome-learn-more',
            58,
        );
    }

    public function render(): void
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable -- The included dashboard view consumes this array.
        $data = [
            'noteCount'        => $this->noteCount(),
            'topicCount'       => $this->topicCount(),
            'eventCount'       => $this->events->count(),
            'latestEvents'     => $this->latestEvents(),
            'components'       => $this->components(),
            'serviceContainer' => $this->serviceContainer(),
            'starter'          => $this->starterConventions(),
            'profiler'         => $this->profiler(),
            'sourceLinks'      => $this->sourceLinks(),
        ];

        require dirname(__DIR__, 2) . '/resources/views/admin/dashboard.php';
    }

    private function noteCount(): int
    {
        if (!function_exists('wp_count_posts')) {
            return 0;
        }

        return (int) (wp_count_posts(Note::POST_TYPE)->publish ?? 0);
    }

    private function topicCount(): int
    {
        if (!function_exists('wp_count_terms')) {
            return 0;
        }

        $count = wp_count_terms([
            'taxonomy'   => Topic::TAXONOMY,
            'hide_empty' => false,
        ]);

        return is_int($count) ? $count : 0;
    }

    /** @return array{collector: class-string, key: string, tag: string, status: string} */
    private function profiler(): array
    {
        return [
            'collector' => DemoProfilerCollector::class,
            'key'       => 'sympress_demo',
            'tag'       => 'profiler.collector',
            'status'    => class_exists(ProfilerBundle::class) ? 'available' : 'missing',
        ];
    }

    /** @return list<array{name: string, package: string, repository: string, status: string}> */
    private function components(): array
    {
        return [
            $this->component('Kernel', 'sympress/kernel', 'https://github.com/SymPress/kernel', App::class),
            $this->component(
                'Events',
                'sympress/event-dispatcher',
                'https://github.com/SymPress/event-dispatcher',
                EventSystem::class,
            ),
            $this->component(
                'Migrations',
                'sympress/migration',
                'https://github.com/SymPress/migration',
                MigrationSystem::class,
            ),
            $this->component('Assets', 'sympress/assets', 'https://github.com/SymPress/assets', AssetManager::class),
            $this->component(
                'Asset Compiler',
                'sympress/asset-compiler',
                'https://github.com/SymPress/asset-compiler',
                AssetCompilerPlugin::class,
            ),
            $this->component(
                'WP-CLI Console',
                'sympress/wp-cli-console',
                'https://github.com/SymPress/wp-cli-console',
                WpCliConsoleBundle::class,
            ),
            $this->component(
                'Monolog Bundle',
                'sympress/monolog-bundle',
                'https://github.com/SymPress/monolog-bundle',
                MonologBundle::class,
            ),
            $this->component('ORM', 'sympress/orm', 'https://github.com/SymPress/orm', EntityManager::class),
            $this->component(
                'Profiler',
                'sympress/profiler',
                'https://github.com/SymPress/profiler',
                ProfilerBundle::class,
            ),
            $this->component(
                'Coding Standards',
                'sympress/coding-standards',
                'https://github.com/SymPress/coding-standards',
                Boundaries::class,
            ),
        ];
    }

    /** @return list<array{name: string, createdAt: string, context: string}> */
    private function latestEvents(): array
    {
        return array_map(
            static fn (DemoEventRecord $event): array => [
                'name'      => $event->eventName,
                'createdAt' => $event->createdAt->format('Y-m-d H:i:s'),
                'context'   => implode(', ', array_keys($event->context)) ?: 'none',
            ],
            $this->events->latest(5),
        );
    }

    /** @return list<array{contract: string, service: string, pattern: string}> */
    private function serviceContainer(): array
    {
        return [
            [
                'contract' => NoteRepositoryInterface::class,
                'service'  => WordPressNoteRepository::class,
                'pattern'  => 'interface alias',
            ],
            [
                'contract' => DemoNoteWriterInterface::class,
                'service'  => WordPressDemoNoteWriter::class,
                'pattern'  => 'writer adapter',
            ],
            [
                'contract' => RemoteQuoteProviderInterface::class,
                'service'  => ZenQuotesQuoteProvider::class,
                'pattern'  => 'remote adapter',
            ],
            [
                'contract' => EntityManager::class,
                'service'  => DemoEventRecordRepository::class,
                'pattern'  => 'ORM repository',
            ],
            [
                'contract' => PluginAssetLocator::class,
                'service'  => 'sympress_demo.plugin_file + sympress_demo.version',
                'pattern'  => 'parameterized service',
            ],
        ];
    }

    /** @return list<array{name: string, package: string, path: string, status: string}> */
    private function starterConventions(): array
    {
        return [
            $this->starterConvention('Console entrypoint', 'sympress/starter', 'bin/console'),
            $this->starterConvention('WPStarter orchestration', 'wecodemore/wpstarter', 'dev-ops/wpstarter.json'),
            $this->starterConvention('Base MU package', 'sympress/starter', 'packages/base-mu-plugins'),
            $this->starterConvention('DDEV runtime', 'sympress/starter', '.ddev/config.yaml'),
        ];
    }

    /** @return list<array{label: string, description: string, path: string, url: string}> */
    private function sourceLinks(): array
    {
        $links = [
            [
                'label'       => 'Service container config',
                'description' => 'autowire, aliases, parameters',
                'path'        => 'packages/sympress-demo/config/services.yaml',
            ],
            [
                'label'       => 'Asset compiler config',
                'description' => 'sympress/asset-compiler',
                'path'        => 'composer.json',
            ],
            [
                'label'       => 'Package asset contract',
                'description' => 'extra.sympress.asset-compiler',
                'path'        => 'packages/sympress-demo/composer.json',
            ],
            [
                'label'       => 'Starter console',
                'description' => 'bin/console',
                'path'        => 'bin/console',
            ],
            [
                'label'       => 'Hook attributes',
                'description' => AsHook::class,
                'path'        => 'packages/sympress-demo/src/Hook/DemoMigrations.php',
            ],
            [
                'label'       => 'REST API adapter',
                'description' => '/wp-json/sympress-demo/v1/notes',
                'path'        => 'packages/sympress-demo/src/Infrastructure/WordPress/RestApiRegistrar.php',
            ],
            [
                'label'       => 'Block editor adapter',
                'description' => BlockRegistrar::BLOCK_NAME,
                'path'        => 'packages/sympress-demo/src/Infrastructure/WordPress/BlockRegistrar.php',
            ],
            [
                'label'       => 'Application service',
                'description' => 'NoteService',
                'path'        => 'packages/sympress-demo/src/Service/NoteService.php',
            ],
            [
                'label'       => 'Seed command adapter',
                'description' => 'wp sympress-demo:create-notes',
                'path'        => 'packages/sympress-demo/src/Command/CreateDemoNotesCommand.php',
            ],
            [
                'label'       => 'Seed use case',
                'description' => 'DemoNoteSeeder',
                'path'        => 'packages/sympress-demo/src/Application/Seed/DemoNoteSeeder.php',
            ],
            [
                'label'       => 'WordPress writer adapter',
                'description' => 'DemoNoteWriterInterface',
                'path'        => 'packages/sympress-demo/src/Infrastructure/WordPress/WordPressDemoNoteWriter.php',
            ],
            [
                'label'       => 'ORM entity',
                'description' => DemoEventRecord::class,
                'path'        => 'packages/sympress-demo/src/Entity/DemoEventRecord.php',
            ],
            [
                'label'       => 'ORM repository',
                'description' => DemoEventRecordRepository::class,
                'path'        => 'packages/sympress-demo/src/Repository/DemoEventRecordRepository.php',
            ],
            [
                'label'       => 'Profiler collector',
                'description' => 'sympress_demo',
                'path'        => 'packages/sympress-demo/src/Profiler/DemoProfilerCollector.php',
            ],
            [
                'label'       => 'Block TypeScript',
                'description' => 'Encore entrypoint',
                'path'        => 'packages/sympress-demo/resources/ts/block-editor.ts',
            ],
        ];

        return array_map(
            static fn (array $link): array => [
                ...$link,
                'url' => 'https://github.com/SymPress/demo/blob/main/' . $link['path'],
            ],
            $links,
        );
    }

    /**
     * @param class-string|string $probeClass
     * @return array{name: string, package: string, repository: string, status: string}
     */
    private function component(string $name, string $package, string $repository, string $probeClass): array
    {
        return [
            'name'       => $name,
            'package'    => $package,
            'repository' => $repository,
            'status'     => class_exists($probeClass) ? 'available' : 'documented',
        ];
    }

    /** @return array{name: string, package: string, path: string, status: string} */
    private function starterConvention(string $name, string $package, string $path): array
    {
        $absolutePath = dirname(__DIR__, 4) . '/' . $path;

        return [
            'name'    => $name,
            'package' => $package,
            'path'    => $path,
            'status'  => file_exists($absolutePath) ? 'available' : 'documented',
        ];
    }
}
