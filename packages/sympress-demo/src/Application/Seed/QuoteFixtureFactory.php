<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class QuoteFixtureFactory
{
    public function create(
        string $quote,
        string $author,
        string $context,
        ?string $source = null,
        ?string $sourceUrl = null,
    ): DemoNoteFixture {
        return new DemoNoteFixture(
            topicSlug: 'quotes',
            title: $author,
            excerpt: $this->excerpt($quote, $author),
            content: [
                $quote,
                $context,
            ],
            source: $source,
            sourceUrl: $sourceUrl,
        );
    }

    private function excerpt(string $quote, string $author): string
    {
        $excerpt = $quote;

        if (function_exists('wp_html_excerpt')) {
            $excerpt = wp_html_excerpt($quote, 140, '...');
        } elseif (strlen($quote) > 140) {
            $excerpt = substr($quote, 0, 137) . '...';
        }

        return sprintf('"%s" - %s', $excerpt, $author);
    }
}
