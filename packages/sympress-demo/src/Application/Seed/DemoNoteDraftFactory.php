<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class DemoNoteDraftFactory
{
    public function create(
        DemoNoteFixture $fixture,
        int $index,
        string $setSlug,
        string $topicOverride = '',
    ): DemoNoteDraft {

        return new DemoNoteDraft(
            topicSlug: $topicOverride !== '' ? $topicOverride : $fixture->topicSlug,
            title: $this->title($fixture, $index),
            excerpt: $fixture->excerpt,
            content: $this->content($fixture, $index, $setSlug),
        );
    }

    private function title(DemoNoteFixture $fixture, int $index): string
    {
        if ($fixture->isQuote()) {
            return sprintf('Quote by %s %02d', $fixture->title, $index);
        }

        return sprintf('%s %02d', $fixture->title, $index);
    }

    private function content(DemoNoteFixture $fixture, int $index, string $setSlug): string
    {
        $paragraphs = array_map(
            fn (string $paragraph): string => sprintf('<p>%s</p>', $this->escapeHtml($paragraph)),
            $fixture->content,
        );

        $paragraphs[] = sprintf(
            '<p><strong>%s</strong> %s</p>',
            $this->escapeHtml('Fixture context:'),
            $this->escapeHtml(sprintf('%s set, generated note #%d.', $setSlug, $index)),
        );

        if ($fixture->source !== null && $fixture->sourceUrl !== null) {
            $paragraphs[] = sprintf(
                '<p><small>%s <a href="%s">%s</a>.</small></p>',
                $this->escapeHtml('Quote source:'),
                $this->escapeUrl($fixture->sourceUrl),
                $this->escapeHtml($fixture->source),
            );
        }

        return implode("\n", $paragraphs);
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function escapeUrl(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
