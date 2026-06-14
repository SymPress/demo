<?php

declare(strict_types=1);

namespace SymPress\Demo\Repository;

use SymPress\Demo\Entity\DemoEventRecord;
use SymPress\Orm\Repository;

final class DemoEventRecordRepository extends Repository
{
    /** @param array<string, mixed> $context */
    public function record(
        string $eventName,
        array $context = [],
        ?\DateTimeImmutable $createdAt = null,
    ): DemoEventRecord {

        $record = DemoEventRecord::record($eventName, $context, $createdAt);

        $this->save($record, flush: true);

        return $record;
    }

    /** @return list<DemoEventRecord> */
    public function latest(int $limit = 5): array
    {
        $records = [];

        foreach ($this->findBy([], ['createdAt' => 'DESC'], max(1, $limit)) as $record) {
            if (!$record instanceof DemoEventRecord) {
                continue;
            }

            $records[] = $record;
        }

        return $records;
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('event')
            ->select('COUNT(event.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
