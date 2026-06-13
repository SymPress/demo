<?php

declare(strict_types=1);

namespace SymPress\Demo\Repository;

use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;

/**
 * WordPress adapter for the note repository contract.
 *
 * WP_Query-style details stay here so the service layer works with plain
 * entity objects.
 */
final class WordPressNoteRepository implements NoteRepositoryInterface
{
    public function findLatest(int $limit = 10): array
    {
        return $this->find([
            'posts_per_page' => $limit,
        ]);
    }

    public function findByTopic(string $topicSlug, int $limit = 10): array
    {
        return $this->find([
            'posts_per_page' => $limit,
            'tax_query' => [
                [
                    'taxonomy' => Topic::TAXONOMY,
                    'field' => 'slug',
                    'terms' => $topicSlug,
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $queryArgs
     * @return list<Note>
     */
    private function find(array $queryArgs): array
    {
        if (!function_exists('get_posts')) {
            return [];
        }

        $posts = get_posts(array_merge([
            'post_type' => Note::POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => false,
        ], $queryArgs));

        return array_values(array_filter(array_map([$this, 'mapPost'], $posts)));
    }

    private function mapPost(object $post): ?Note
    {
        $id = (int) ($post->ID ?? 0);

        if ($id <= 0) {
            return null;
        }

        return new Note(
            id: $id,
            title: $this->postTitle($id),
            excerpt: $this->postExcerpt($id, (string) ($post->post_content ?? '')),
            url: $this->postUrl($id),
            topic: $this->firstTopic($id),
            publishedAt: $this->publishedAt((string) ($post->post_date_gmt ?? $post->post_date ?? 'now')),
        );
    }

    private function postTitle(int $postId): string
    {
        if (!function_exists('get_the_title')) {
            return sprintf('Note #%d', $postId);
        }

        return (string) get_the_title($postId);
    }

    private function postExcerpt(int $postId, string $content): string
    {
        if (function_exists('has_excerpt') && has_excerpt($postId) && function_exists('get_the_excerpt')) {
            return (string) get_the_excerpt($postId);
        }

        if (function_exists('wp_trim_words') && function_exists('wp_strip_all_tags')) {
            return (string) wp_trim_words(wp_strip_all_tags($content), 32);
        }

        return trim(strip_tags($content));
    }

    private function postUrl(int $postId): string
    {
        if (!function_exists('get_permalink')) {
            return '';
        }

        $url = get_permalink($postId);

        return is_string($url) ? $url : '';
    }

    private function firstTopic(int $postId): ?Topic
    {
        if (!function_exists('get_the_terms')) {
            return null;
        }

        $terms = get_the_terms($postId, Topic::TAXONOMY);

        if (!is_array($terms) || $terms === []) {
            return null;
        }

        $term = reset($terms);

        if (!is_object($term)) {
            return null;
        }

        return new Topic(
            id: (int) $term->term_id,
            name: (string) $term->name,
            slug: (string) $term->slug,
            url: $this->termUrl($term),
        );
    }

    private function termUrl(object $term): string
    {
        if (!function_exists('get_term_link')) {
            return '';
        }

        $url = get_term_link($term);

        return is_string($url) ? $url : '';
    }

    private function publishedAt(string $date): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($date !== '' ? $date : 'now', new \DateTimeZone('UTC'));
        } catch (\Throwable) {
            return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }
    }
}
