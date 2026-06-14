<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;
use SymPress\Kernel\Attribute\AsHook;

final readonly class TaxonomyRegistrar
{
    #[AsHook('init')]
    public function register(): void
    {
        register_taxonomy(Topic::TAXONOMY, [Note::POST_TYPE], [
            'labels'            => [
                'name'          => __('Topics', 'sympress-demo'),
                'singular_name' => __('Topic', 'sympress-demo'),
                'search_items'  => __('Search Topics', 'sympress-demo'),
                'all_items'     => __('All Topics', 'sympress-demo'),
                'edit_item'     => __('Edit Topic', 'sympress-demo'),
                'add_new_item'  => __('Add New Topic', 'sympress-demo'),
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => [
                'slug' => 'knowledge-topic',
            ],
        ]);
    }
}
