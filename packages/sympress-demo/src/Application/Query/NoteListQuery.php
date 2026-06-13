<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Query;

final readonly class NoteListQuery
{
    public const int MIN_LIMIT = 1;
    public const int MAX_LIMIT = 50;
    public const int DEFAULT_LIMIT = 10;

    private function __construct(
        public int $limit,
        public string $topicSlug,
    ) {
    }

    public static function create(int $limit = self::DEFAULT_LIMIT, string $topicSlug = ''): self
    {
        return new self(
            max(self::MIN_LIMIT, min(self::MAX_LIMIT, $limit)),
            trim($topicSlug),
        );
    }

    public function hasTopic(): bool
    {
        return $this->topicSlug !== '';
    }
}
