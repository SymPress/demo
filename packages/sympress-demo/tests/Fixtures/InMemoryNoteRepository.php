<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Fixtures;

use SymPress\Demo\Entity\Note;
use SymPress\Demo\Repository\NoteRepositoryInterface;

final class InMemoryNoteRepository implements NoteRepositoryInterface
{
    public int $latestLimit = 0;
    public int $topicLimit = 0;
    public string $topicSlug = '';

    /**
     * @param list<Note> $notes
     */
    public function __construct(
        private readonly array $notes,
    ) {
    }

    public function findLatest(int $limit = 10): array
    {
        $this->latestLimit = $limit;

        return array_slice($this->notes, 0, $limit);
    }

    public function findByTopic(string $topicSlug, int $limit = 10): array
    {
        $this->topicSlug = $topicSlug;
        $this->topicLimit = $limit;

        return array_slice(
            array_values(array_filter(
                $this->notes,
                static fn (Note $note): bool => $note->topic?->slug === $topicSlug,
            )),
            0,
            $limit,
        );
    }
}
