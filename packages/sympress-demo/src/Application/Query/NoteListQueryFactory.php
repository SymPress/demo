<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Query;

use SymPress\Demo\Support\SlugNormalizer;

final readonly class NoteListQueryFactory
{
    public function __construct(
        private SlugNormalizer $slugs,
    ) {
    }

    public function fromInput(mixed $limit, mixed $topic): NoteListQuery
    {
        return NoteListQuery::create(
            $this->normalizeLimit($limit),
            $this->slugs->normalize($topic),
        );
    }

    private function normalizeLimit(mixed $value): int
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return NoteListQuery::DEFAULT_LIMIT;
        }

        return (int) $value;
    }
}
