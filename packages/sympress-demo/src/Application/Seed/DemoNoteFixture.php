<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class DemoNoteFixture
{
    /**
     * @param non-empty-list<string> $content
     */
    public function __construct(
        public string $topicSlug,
        public string $title,
        public string $excerpt,
        public array $content,
        public ?string $source = null,
        public ?string $sourceUrl = null,
    ) {
    }

    public function isQuote(): bool
    {
        return $this->topicSlug === 'quotes';
    }
}
