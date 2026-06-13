<?php

declare(strict_types=1);

namespace SymPress\Demo\Event;

use SymPress\Demo\Entity\Note;

final readonly class NoteRenderedEvent
{
    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        public Note $note,
        public array $context,
        public \DateTimeImmutable $renderedAt,
    ) {
    }
}
