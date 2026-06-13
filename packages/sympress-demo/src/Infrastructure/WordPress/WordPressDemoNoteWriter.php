<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

use SymPress\Demo\Application\Seed\DemoNoteDraft;
use SymPress\Demo\Application\Seed\DemoNoteWriterInterface;
use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;

final class WordPressDemoNoteWriter implements DemoNoteWriterInterface
{
    public function deleteAll(): int
    {
        if (!function_exists('get_posts')) {
            return 0;
        }

        $postIds = get_posts([
            'post_type' => Note::POST_TYPE,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
        ]);

        if (!is_array($postIds)) {
            return 0;
        }

        $deleted = 0;

        foreach ($postIds as $postId) {
            if (wp_delete_post((int) $postId, true) !== false) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function save(DemoNoteDraft $draft): ?string
    {
        try {
            $termId = $this->ensureTopic($draft->topicSlug);
        } catch (\RuntimeException $exception) {
            return $exception->getMessage();
        }

        $postId = wp_insert_post([
            'post_type' => Note::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $draft->title,
            'post_excerpt' => $draft->excerpt,
            'post_content' => $draft->content,
        ], true);

        if (is_wp_error($postId)) {
            return $postId->get_error_message();
        }

        wp_set_object_terms((int) $postId, [$termId], Topic::TAXONOMY);

        return null;
    }

    private function ensureTopic(string $topicSlug): int
    {
        $topicSlug = $topicSlug !== '' ? $topicSlug : 'architecture';
        $term = term_exists($topicSlug, Topic::TAXONOMY);

        if (is_array($term)) {
            return (int) $term['term_id'];
        }

        if (is_numeric($term)) {
            return (int) $term;
        }

        $created = wp_insert_term(
            ucwords(str_replace('-', ' ', $topicSlug)),
            Topic::TAXONOMY,
            ['slug' => $topicSlug],
        );

        if (is_wp_error($created)) {
            throw new \RuntimeException($created->get_error_message());
        }

        return (int) $created['term_id'];
    }
}
