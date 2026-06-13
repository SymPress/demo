<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

interface DemoNoteWriterInterface
{
    public function deleteAll(): int;

    public function save(DemoNoteDraft $draft): ?string;
}
