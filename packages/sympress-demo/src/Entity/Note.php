<?php

declare(strict_types=1);

namespace SymPress\Demo\Entity;

final readonly class Note
{
    public const string POST_TYPE = 'sympress_note';

    public function __construct(
        public int $id,
        public string $title,
        public string $excerpt,
        public string $url,
        public ?Topic $topic,
        public \DateTimeImmutable $publishedAt,
    ) {
    }

    public function topicName(): string
    {
        return $this->topic === null ? 'General' : $this->topic->name;
    }
}
