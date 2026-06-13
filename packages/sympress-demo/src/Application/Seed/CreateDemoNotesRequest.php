<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class CreateDemoNotesRequest
{
    public const int MIN_COUNT = 1;
    public const int MAX_COUNT = 50;
    public const int DEFAULT_COUNT = 5;

    public function __construct(
        public int $count = self::DEFAULT_COUNT,
        public string $setSlug = 'quotes',
        public string $sourceSlug = 'zenquotes',
        public string $topicOverride = '',
        public bool $reset = false,
    ) {
    }
}
