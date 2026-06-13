<?php

declare(strict_types=1);

namespace SymPress\Demo\EventSubscriber;

use Psr\Log\LoggerInterface;
use SymPress\Demo\Event\NoteRenderedEvent;
use SymPress\Demo\Infrastructure\Persistence\DemoEventRepository;
use SymPress\EventDispatcher\Contract\EventSubscriberInterface;
use SymPress\EventDispatcher\Contract\ListenerRegistryInterface;

final readonly class LogRenderedNoteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private DemoEventRepository $events,
    ) {
    }

    /** @return array<class-string, string> */
    public static function getSubscribedEvents(): array
    {
        return [
            NoteRenderedEvent::class => 'onNoteRendered',
        ];
    }

    public function register(ListenerRegistryInterface $dispatcher): void
    {
        $dispatcher->addSubscriber($this);
    }

    public function onNoteRendered(NoteRenderedEvent $event): void
    {
        $context = [
            'note_id' => $event->note->id,
            'title'   => $event->note->title,
            'topic'   => $event->note->topic?->slug,
            'source'  => $event->context['source'] ?? null,
        ];

        $this->logger->info('SymPress Demo note processed.', $context);
        $this->events->record(NoteRenderedEvent::class, $context);
    }
}
