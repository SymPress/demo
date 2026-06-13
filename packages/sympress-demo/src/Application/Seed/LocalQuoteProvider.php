<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class LocalQuoteProvider implements QuoteProviderInterface
{
    public function __construct(
        private QuoteFixtureFactory $fixtures,
    ) {
    }

    public function quotes(int $limit): array
    {
        $quotes = [
            [
                'quote'   => 'The public is wonderfully tolerant. It forgives everything except genius.',
                'author'  => 'Oscar Wilde',
                'context' => 'A literature note bundled as a local fallback for the quote seed command.',
            ],
            [
                'quote'   => 'Without music, life would be a mistake.',
                'author'  => 'Friedrich Nietzsche',
                'context' => 'A music note bundled as a local fallback for the quote seed command.',
            ],
            [
                'quote'   => 'The road of excess leads to the palace of wisdom.',
                'author'  => 'William Blake',
                'context' => 'An artist-poet note bundled as a local fallback for the quote seed command.',
            ],
            [
                'quote'   => 'Beware; for I am fearless, and therefore powerful.',
                'author'  => 'Mary Shelley',
                'context' => 'A literature note bundled as a local fallback for the quote seed command.',
            ],
            [
                'quote'   => 'Great things are done by a series of small things brought together.',
                'author'  => 'Vincent van Gogh',
                'context' => 'An artist note bundled as a local fallback for the quote seed command.',
            ],
        ];

        return array_map(
            fn (array $quote): DemoNoteFixture => $this->fixtures->create(
                $quote['quote'],
                $quote['author'],
                $quote['context'],
            ),
            array_slice($quotes, 0, max(1, $limit)),
        );
    }
}
