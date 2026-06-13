<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymPress\Demo\Application\Query\NoteListQuery;
use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;
use SymPress\Demo\Service\NoteService;
use SymPress\Demo\Tests\Fixtures\InMemoryNoteRepository;

final class NoteServiceTest extends TestCase
{
    public function testLatestNotesAreLoadedThroughTheRepositoryWithoutSideEffects(): void
    {
        $note = $this->note('Dependency Injection', 'architecture');
        $repository = new InMemoryNoteRepository([$note]);
        $service = new NoteService($repository);

        $notes = $service->getLatestNotes(5);

        self::assertSame([$note], $notes);
        self::assertSame(5, $repository->latestLimit);
    }

    public function testTopicFilterUsesRepositoryAndPreservesContext(): void
    {
        $matching = $this->note('Events', 'architecture');
        $other = $this->note('Assets', 'frontend');
        $repository = new InMemoryNoteRepository([$matching, $other]);
        $service = new NoteService($repository);

        $notes = $service->getNotesByTopic('architecture', 20);

        self::assertSame([$matching], $notes);
        self::assertSame('architecture', $repository->topicSlug);
        self::assertSame(20, $repository->topicLimit);
    }

    public function testListQueryCanDriveTopicLookups(): void
    {
        $matching = $this->note('Events', 'architecture');
        $repository = new InMemoryNoteRepository([$matching]);
        $service = new NoteService($repository);

        $notes = $service->list(NoteListQuery::create(7, 'architecture'));

        self::assertSame([$matching], $notes);
        self::assertSame('architecture', $repository->topicSlug);
        self::assertSame(7, $repository->topicLimit);
    }

    public function testLimitIsClampedToKeepBlockAndRestQueriesPredictable(): void
    {
        $note = $this->note('Migrations', 'database');
        $repository = new InMemoryNoteRepository([$note]);
        $service = new NoteService($repository);

        $service->getLatestNotes(500);

        self::assertSame(50, $repository->latestLimit);
    }

    private function note(string $title, string $topic): Note
    {
        return new Note(
            id: strlen($title) + strlen($topic),
            title: $title,
            excerpt: 'A test note.',
            url: 'https://example.test/' . strtolower(str_replace(' ', '-', $title)),
            topic: new Topic(1, ucfirst($topic), $topic),
            publishedAt: new \DateTimeImmutable('2026-06-10 12:00:00'),
        );
    }
}
