<?php

declare(strict_types=1);

namespace SymPress\Demo\Hook;

use SymPress\Demo\Migration\CreateDemoEventsTableMigration;
use SymPress\WordPress\Migration\Application\MigrationSystem;

final readonly class DemoMigrations
{
    private const string PLUGIN_SLUG = 'sympress-demo';

    public function __construct(
        private CreateDemoEventsTableMigration $createDemoEventsTable,
    ) {
    }

    public function register(MigrationSystem $system): void
    {
        $system->registerMigrations(
            self::PLUGIN_SLUG,
            [
                $this->createDemoEventsTable,
            ],
        );
    }

    public function migrate(MigrationSystem $system): void
    {
        $manager = $system->createMigrationManager(self::PLUGIN_SLUG);

        if ($manager === null || !$manager->hasPendingMigrations()) {
            return;
        }

        $manager->migrateTo();
    }
}
