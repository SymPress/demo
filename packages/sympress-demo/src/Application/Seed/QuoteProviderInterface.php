<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

interface QuoteProviderInterface
{
    /**
     * @return list<DemoNoteFixture>
     */
    public function quotes(int $limit): array;
}
