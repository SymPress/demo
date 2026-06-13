<?php

declare(strict_types=1);

namespace SymPress\Demo\Presentation;

use SymPress\Demo\Entity\Note;

final readonly class NoteResourceFactory
{
    /**
     * @param list<Note> $notes
     * @return list<array{
     *     id: int,
     *     title: string,
     *     excerpt: string,
     *     url: string,
     *     topic: array{name: string, slug: string}|null,
     *     published_at: string
     * }>
     */
    public function collection(array $notes): array
    {
        return array_map([$this, 'item'], $notes);
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     excerpt: string,
     *     url: string,
     *     topic: array{name: string, slug: string}|null,
     *     published_at: string
     * }
     */
    public function item(Note $note): array
    {
        return [
            'id'           => $note->id,
            'title'        => $note->title,
            'excerpt'      => $note->excerpt,
            'url'          => $note->url,
            'topic'        => $note->topic === null
                ? null
                : [
                    'name' => $note->topic->name,
                    'slug' => $note->topic->slug,
                ],
            'published_at' => $note->publishedAt->format(DATE_ATOM),
        ];
    }
}
