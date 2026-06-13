<?php

declare(strict_types=1);

namespace SymPress\Demo\Migration;

use SymPress\WordPress\Migration\Domain\AbstractMigration;

final class CreateDemoEventsTableMigration extends AbstractMigration
{
    protected const string VERSION = '2026.06.10.001';

    #[\Override]
    public function up(): string
    {
        return sprintf(
            'CREATE TABLE %ssympress_demo_events (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                event_name VARCHAR(191) NOT NULL,
                context LONGTEXT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY  (id),
                KEY event_name (event_name),
                KEY created_at (created_at)
            ) %s;',
            $this->prefix,
            $this->charsetCollate,
        );
    }

    #[\Override]
    public function down(): string
    {
        return sprintf('DROP TABLE IF EXISTS %ssympress_demo_events;', $this->prefix);
    }
}
