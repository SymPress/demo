<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

use SymPress\Demo\Support\SlugNormalizer;

final readonly class CreateDemoNotesRequestFactory
{
    public function __construct(
        private SlugNormalizer $slugs,
    ) {
    }

    /** @param array<string, mixed> $assocArgs */
    public function fromWpCliArgs(array $assocArgs): CreateDemoNotesRequest
    {
        return new CreateDemoNotesRequest(
            count: $this->normalizeCount($assocArgs['count'] ?? CreateDemoNotesRequest::DEFAULT_COUNT),
            setSlug: $this->slugs->normalize($assocArgs['set'] ?? 'quotes', 'quotes'),
            sourceSlug: $this->slugs->normalize($assocArgs['source'] ?? 'zenquotes', 'zenquotes'),
            topicOverride: array_key_exists('topic', $assocArgs)
                ? $this->slugs->normalize($assocArgs['topic'] ?? '')
                : '',
            reset: array_key_exists('reset', $assocArgs),
        );
    }

    private function normalizeCount(mixed $value): int
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return CreateDemoNotesRequest::DEFAULT_COUNT;
        }

        return max(
            CreateDemoNotesRequest::MIN_COUNT,
            min(CreateDemoNotesRequest::MAX_COUNT, (int) $value),
        );
    }
}
