<?php

declare(strict_types=1);

namespace SymPress\Demo\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use SymPress\Demo\Event\NoteRenderedEvent;
use SymPress\Demo\Infrastructure\Persistence\DemoEventRepository;
use SymPress\EventDispatcher\Application\EventSystem;
use SymPress\EventDispatcher\Attribute\AsEventListener;
use SymPress\EventDispatcher\Contract\ListenerRegistryInterface;
use SymPress\Kernel\Attribute\AsHook;

#[WithMonologChannel('sympress_demo')]
final readonly class LogRenderedNoteSubscriber
{
    public function __construct(
        private LoggerInterface $logger,
        private DemoEventRepository $events,
    ) {
    }

    #[AsHook(EventSystem::REGISTER_HOOK, acceptedArgs: 1)]
    public function register(ListenerRegistryInterface $dispatcher): void
    {
        $dispatcher->register($this);
    }

    #[AsEventListener(event: NoteRenderedEvent::class)]
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
