<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

use SymPress\Demo\Entity\Note;

final readonly class CustomPostTypeRegistrar
{
    public function register(): void
    {
        register_post_type(Note::POST_TYPE, [
            'labels' => [
                'name' => __('Knowledge Notes', 'sympress-demo'),
                'singular_name' => __('Knowledge Note', 'sympress-demo'),
                'add_new_item' => __('Add New Note', 'sympress-demo'),
                'edit_item' => __('Edit Note', 'sympress-demo'),
                'new_item' => __('New Note', 'sympress-demo'),
                'view_item' => __('View Note', 'sympress-demo'),
                'search_items' => __('Search Notes', 'sympress-demo'),
            ],
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'has_archive' => true,
            'rewrite' => [
                'slug' => 'knowledge-notes',
            ],
        ]);
    }
}
