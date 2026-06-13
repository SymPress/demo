<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class DemoNoteFixtureSelection
{
    /**
     * @param non-empty-list<DemoNoteFixture> $fixtures
     * @param list<string> $warnings
     */
    public function __construct(
        public array $fixtures,
        public string $setSlug,
        public array $warnings = [],
    ) {
    }
}
