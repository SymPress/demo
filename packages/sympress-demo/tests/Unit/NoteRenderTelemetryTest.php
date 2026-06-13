<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymPress\Demo\Application\Query\NoteListQuery;
use SymPress\Demo\Application\Telemetry\NoteRenderTelemetry;
use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;
use SymPress\Demo\Event\NoteRenderedEvent;
use SymPress\Demo\Tests\Fixtures\RecordingEventDispatcher;

final class NoteRenderTelemetryTest extends TestCase
{
    public function testTelemetryIsDisabledByDefault(): void
    {
        $events = new RecordingEventDispatcher();
        $telemetry = new NoteRenderTelemetry($events);

        $telemetry->record(NoteListQuery::create(5), [$this->note()], 'rest');

        self::assertSame([], $events->events);
    }

    public function testEnabledTelemetryDispatchesRenderedNoteEventsWithContext(): void
    {
        $events = new RecordingEventDispatcher();
        $telemetry = new NoteRenderTelemetry($events, true);

        $telemetry->record(NoteListQuery::create(5, 'architecture'), [$this->note()], 'block');

        self::assertCount(1, $events->events);
        $event = $events->events[0];
        self::assertInstanceOf(NoteRenderedEvent::class, $event);
        self::assertSame('topic', $event->context['source']);
        self::assertSame('architecture', $event->context['topic']);
        self::assertSame('block', $event->context['surface']);
    }

    private function note(): Note
    {
        return new Note(
            id: 42,
            title: 'Dependency Injection',
            excerpt: 'A test note.',
            url: 'https://example.test/dependency-injection',
            topic: new Topic(1, 'Architecture', 'architecture'),
            publishedAt: new \DateTimeImmutable('2026-06-10 12:00:00'),
        );
    }
}
