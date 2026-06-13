<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Telemetry;

use Psr\EventDispatcher\EventDispatcherInterface;
use SymPress\Demo\Application\Query\NoteListQuery;
use SymPress\Demo\Entity\Note;
use SymPress\Demo\Event\NoteRenderedEvent;

final readonly class NoteRenderTelemetry
{
    public function __construct(
        private EventDispatcherInterface $events,
        private bool $enabled = false,
    ) {
    }

    /**
     * @param list<Note> $notes
     */
    public function record(NoteListQuery $query, array $notes, string $surface): void
    {
        if (!$this->enabled) {
            return;
        }

        $context = [
            'source' => $query->hasTopic() ? 'topic' : 'latest',
            'surface' => $surface,
        ];

        if ($query->hasTopic()) {
            $context['topic'] = $query->topicSlug;
        }

        foreach ($notes as $note) {
            $this->events->dispatch(
                new NoteRenderedEvent(
                    note: $note,
                    context: $context,
                    renderedAt: new \DateTimeImmutable(),
                ),
            );
        }
    }
}
