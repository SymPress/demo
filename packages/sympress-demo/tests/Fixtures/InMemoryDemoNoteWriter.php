<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Fixtures;

use SymPress\Demo\Application\Seed\DemoNoteDraft;
use SymPress\Demo\Application\Seed\DemoNoteWriterInterface;

final class InMemoryDemoNoteWriter implements DemoNoteWriterInterface
{
    public int $deleted = 0;

    /** @var list<DemoNoteDraft> */
    public array $drafts = [];

    /** @var list<string> */
    public array $errors = [];

    public function deleteAll(): int
    {
        return $this->deleted;
    }

    public function save(DemoNoteDraft $draft): ?string
    {
        $this->drafts[] = $draft;

        return array_shift($this->errors);
    }
}
