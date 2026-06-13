<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

use SymPress\Demo\Application\Query\NoteListQueryFactory;
use SymPress\Demo\Presentation\NoteResourceFactory;
use SymPress\Demo\Service\NoteService;

/**
 * Exposes the demo application service through the WordPress REST API.
 */
final readonly class RestApiRegistrar
{
    private const string ROUTE_NAMESPACE = 'sympress-demo/v1';
    private const string ROUTE_NOTES = '/notes';

    public function __construct(
        private NoteService $notes,
        private NoteListQueryFactory $queries,
        private NoteResourceFactory $resources,
    ) {
    }

    public function register(): void
    {
        if (!function_exists('register_rest_route')) {
            return;
        }

        register_rest_route(self::ROUTE_NAMESPACE, self::ROUTE_NOTES, [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'listNotes'],
                'permission_callback' => static fn (): bool => true,
                'args'                => [
                    'limit' => [
                        'description'       => __('Maximum number of notes to return.', 'sympress-demo'),
                        'type'              => 'integer',
                        'default'           => 10,
                        'minimum'           => 1,
                        'maximum'           => 50,
                        'sanitize_callback' => static fn (mixed $value): int => function_exists('absint')
                            ? absint($value)
                            : max(0, (int) $value),
                    ],
                    'topic' => [
                        'description'       => __('Optional topic slug used to filter notes.', 'sympress-demo'),
                        'type'              => 'string',
                        'default'           => '',
                        'sanitize_callback' => static fn (mixed $value): string => function_exists('sanitize_title')
                            ? sanitize_title((string) $value)
                            : trim((string) $value),
                    ],
                ],
            ],
        ]);
    }

    public function listNotes(\WP_REST_Request $request): \WP_REST_Response
    {
        $notes = $this->notes->list($this->queries->fromInput(
            $request->get_param('limit'),
            $request->get_param('topic'),
        ));

        return new \WP_REST_Response([
            'items' => $this->resources->collection($notes),
            'count' => count($notes),
            'links' => [
                'collection' => $this->collectionUrl(),
            ],
        ]);
    }

    private function collectionUrl(): string
    {
        if (function_exists('rest_url')) {
            return rest_url(self::ROUTE_NAMESPACE . self::ROUTE_NOTES);
        }

        return '/' . self::ROUTE_NAMESPACE . self::ROUTE_NOTES;
    }
}
