<?php

declare(strict_types=1);

namespace SymPress\Demo\Entity;

use SymPress\Demo\Repository\DemoEventRecordRepository;
use SymPress\Orm\Mapping\Column;
use SymPress\Orm\Mapping\Entity;
use SymPress\Orm\Mapping\GeneratedValue;
use SymPress\Orm\Mapping\Id;
use SymPress\Orm\Mapping\Index;

#[Entity(table: 'sympress_demo_events', repositoryClass: DemoEventRecordRepository::class)]
#[Index(name: 'event_name', columns: ['eventName'])]
#[Index(name: 'created_at', columns: ['createdAt'])]
final class DemoEventRecord
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'bigint', unsigned: true)]
    public ?int $id = null;

    #[Column(name: 'event_name', type: 'string', length: 191)]
    public string $eventName;

    /** @var array<string, mixed> */
    #[Column(type: 'json', nullable: true)]
    public array $context = [];

    #[Column(name: 'created_at', type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    /** @param array<string, mixed> $context */
    public static function record(
        string $eventName,
        array $context = [],
        ?\DateTimeImmutable $createdAt = null,
    ): self {
        $record = new self();
        $record->eventName = $eventName;
        $record->context = $context;
        $record->createdAt = $createdAt ?? new \DateTimeImmutable();

        return $record;
    }

    /** @param array<string, mixed> $context */
    public static function restore(
        ?int $id,
        string $eventName,
        array $context,
        \DateTimeImmutable $createdAt,
    ): self {
        $record = self::record($eventName, $context, $createdAt);
        $record->id = $id;

        return $record;
    }
}
