<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class CreateDemoNotesResult
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        public int $created,
        public int $deleted,
        public string $setSlug,
        public array $warnings = [],
    ) {
    }
}
