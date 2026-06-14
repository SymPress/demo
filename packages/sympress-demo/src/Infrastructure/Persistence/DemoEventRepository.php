<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\Persistence;

use SymPress\Demo\Entity\DemoEventRecord;
use SymPress\Demo\Repository\DemoEventRecordRepository;
use SymPress\Orm\EntityManager;

/**
 * Small persistence adapter for demo events.
 *
 * Logging side effects stay outside the domain service. The table schema is
 * owned by SymPress\Demo\Migration\CreateDemoEventsTableMigration and mapped
 * by SymPress\Demo\Entity\DemoEventRecord for ORM reads and writes.
 */
final readonly class DemoEventRepository
{
    public function __construct(
        private EntityManager $entities,
    ) {
    }

    /** @param array<string, mixed> $context */
    public function record(string $eventName, array $context = []): void
    {
        $repository = $this->ormRepository();

        if ($repository instanceof DemoEventRecordRepository) {
            try {
                $repository->record($eventName, $context);

                return;
            } catch (\Throwable) {
                // Fall through to the WordPress adapter while migrations catch up.
            }
        }

        $database = $this->database();

        if ($database === null) {
            return;
        }

        $database->insert(
            $this->table($database),
            [
                'event_name' => $eventName,
                'context'    => $this->encodeContext($context),
                'created_at' => $this->currentTime(),
            ],
            ['%s', '%s', '%s'],
        );
    }

    public function count(): int
    {
        $repository = $this->ormRepository();

        if ($repository instanceof DemoEventRecordRepository) {
            try {
                return $repository->countAll();
            } catch (\Throwable) {
                // Fall through to the WordPress adapter while migrations catch up.
            }
        }

        $database = $this->database();

        if ($database === null) {
            return 0;
        }

        return (int) $database->get_var(sprintf('SELECT COUNT(*) FROM %s', $this->table($database)));
    }

    /** @return list<DemoEventRecord> */
    public function latest(int $limit = 5): array
    {
        $repository = $this->ormRepository();

        if ($repository instanceof DemoEventRecordRepository) {
            try {
                return $repository->latest($limit);
            } catch (\Throwable) {
                // Fall through to the WordPress adapter while migrations catch up.
            }
        }

        $database = $this->database();

        if ($database === null) {
            return [];
        }

        $limit = max(1, min(20, $limit));
        $sql = $database->prepare(
            sprintf(
                'SELECT id, event_name, context, created_at FROM %s ORDER BY created_at DESC LIMIT %%d',
                $this->table($database),
            ),
            $limit,
        );

        $rows = $database->get_results($sql, defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A');

        if (!is_array($rows)) {
            return [];
        }

        $records = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $records[] = DemoEventRecord::restore(
                isset($row['id']) ? (int) $row['id'] : null,
                is_scalar($row['event_name'] ?? null) ? (string) $row['event_name'] : 'unknown',
                $this->decodeContext($row['context'] ?? null),
                $this->dateTime($row['created_at'] ?? null),
            );
        }

        return $records;
    }

    private function ormRepository(): ?DemoEventRecordRepository
    {
        try {
            $repository = $this->entities->getRepository(DemoEventRecord::class);
        } catch (\Throwable) {
            return null;
        }

        return $repository instanceof DemoEventRecordRepository ? $repository : null;
    }

    private function database(): ?\wpdb
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable -- WordPress exposes wpdb through the global registry.
        $database = $GLOBALS['wpdb'] ?? null;

        return $database instanceof \wpdb ? $database : null;
    }

    private function table(\wpdb $database): string
    {
        return $database->prefix . 'sympress_demo_events';
    }

    /** @param array<string, mixed> $context */
    private function encodeContext(array $context): string
    {
        if (!function_exists('wp_json_encode')) {
            return '{}';
        }

        $encoded = wp_json_encode($context);

        return is_string($encoded) ? $encoded : '{}';
    }

    /** @return array<string, mixed> */
    private function decodeContext(mixed $context): array
    {
        $decoded = json_decode(is_scalar($context) ? (string) $context : '{}', true);

        return is_array($decoded) ? $decoded : [];
    }

    private function currentTime(): string
    {
        if (function_exists('current_time')) {
            return current_time('mysql', true);
        }

        return (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    private function dateTime(mixed $value): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable(is_scalar($value) ? (string) $value : 'now');
        } catch (\Throwable) {
            return new \DateTimeImmutable();
        }
    }
}
