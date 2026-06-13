<?php

declare(strict_types=1);

namespace SymPress\Demo\Service;

use SymPress\Demo\Application\Query\NoteListQuery;
use SymPress\Demo\Entity\Note;
use SymPress\Demo\Repository\NoteRepositoryInterface;

/**
 * Application service for note retrieval.
 *
 * It depends on contracts, not WordPress functions, so the behavior can be
 * tested without booting WordPress.
 */
final readonly class NoteService
{
    public function __construct(
        private NoteRepositoryInterface $notes,
    ) {
    }

    /**
     * @return list<Note>
     */
    public function list(NoteListQuery $query): array
    {
        if ($query->hasTopic()) {
            return $this->notes->findByTopic($query->topicSlug, $query->limit);
        }

        return $this->notes->findLatest($query->limit);
    }

    /**
     * @return list<Note>
     */
    public function getLatestNotes(int $limit = 10): array
    {
        return $this->list(NoteListQuery::create($limit));
    }

    /**
     * @return list<Note>
     */
    public function getNotesByTopic(string $topicSlug, int $limit = 10): array
    {
        return $this->list(NoteListQuery::create($limit, trim($topicSlug)));
    }
}
