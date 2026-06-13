<?php

declare(strict_types=1);

namespace SymPress\Demo\Repository;

use SymPress\Demo\Entity\Note;

interface NoteRepositoryInterface
{
    /** @return list<Note> */
    public function findLatest(int $limit = 10): array;

    /** @return list<Note> */
    public function findByTopic(string $topicSlug, int $limit = 10): array;
}
