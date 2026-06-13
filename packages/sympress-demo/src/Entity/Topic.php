<?php

declare(strict_types=1);

namespace SymPress\Demo\Entity;

final readonly class Topic
{
    public const string TAXONOMY = 'sympress_topic';

    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $url = '',
    ) {
    }
}
