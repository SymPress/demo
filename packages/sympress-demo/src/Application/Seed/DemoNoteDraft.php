<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class DemoNoteDraft
{
    public function __construct(
        public string $topicSlug,
        public string $title,
        public string $excerpt,
        public string $content,
    ) {
    }
}
