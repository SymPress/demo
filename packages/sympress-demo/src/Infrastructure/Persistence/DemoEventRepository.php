<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\Persistence;

/**
 * Small persistence adapter for demo events.
 *
 * Logging side effects stay outside the domain service. The table schema is
 * owned by SymPress\Demo\Migration\CreateDemoEventsTableMigration.
 */
final readonly class DemoEventRepository
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(string $eventName, array $context = []): void
    {
        $database = $this->database();

        if ($database === null) {
            return;
        }

        $database->insert(
            $this->table($database),
            [
                'event_name' => $eventName,
                'context' => wp_json_encode($context),
                'created_at' => current_time('mysql', true),
            ],
            ['%s', '%s', '%s'],
        );
    }

    public function count(): int
    {
        $database = $this->database();

        if ($database === null) {
            return 0;
        }

        return (int) $database->get_var(sprintf('SELECT COUNT(*) FROM %s', $this->table($database)));
    }

    private function database(): ?\wpdb
    {
        $database = $GLOBALS['wpdb'] ?? null;

        return $database instanceof \wpdb ? $database : null;
    }

    private function table(\wpdb $database): string
    {
        return $database->prefix . 'sympress_demo_events';
    }
}
