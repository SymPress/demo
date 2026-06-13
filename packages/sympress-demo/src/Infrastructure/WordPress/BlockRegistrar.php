<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

use SymPress\Demo\Application\Query\NoteListQueryFactory;
use SymPress\Demo\Application\Telemetry\NoteRenderTelemetry;
use SymPress\Demo\Service\NoteService;
use SymPress\Demo\Support\PluginAssetLocator;
use SymPress\Demo\Support\TemplateRenderer;

/**
 * Registers the dynamic block editor example for the notes application.
 */
final readonly class BlockRegistrar
{
    public const string BLOCK_NAME = 'sympress-demo/notes';

    private const string EDITOR_SCRIPT = 'sympress-demo-block-editor';
    private const string EDITOR_SCRIPT_FILE = 'sympress-demo-block-editor.js';

    public function __construct(
        private NoteService $notes,
        private NoteListQueryFactory $queries,
        private NoteRenderTelemetry $telemetry,
        private TemplateRenderer $renderer,
        private PluginAssetLocator $assets,
    ) {
    }

    public function register(): void
    {
        if (!function_exists('register_block_type') || !is_readable($this->blockPath() . '/block.json')) {
            return;
        }

        $this->registerEditorScript();

        register_block_type($this->blockPath(), [
            'render_callback' => [$this, 'render'],
        ]);
    }

    /** @param array{limit?: mixed, topic?: mixed} $attributes */
    public function render(array $attributes = [], string $content = '', ?object $block = null): string
    {
        unset($content, $block);

        $query = $this->queries->fromInput($attributes['limit'] ?? 5, $attributes['topic'] ?? '');
        $notes = $this->notes->list($query);
        $this->telemetry->record($query, $notes, 'block');

        return $this->renderer->render('frontend/notes-list.php', [
            'notes' => $notes,
            'topic' => $query->topicSlug,
            'limit' => $query->limit,
        ]);
    }

    private function registerEditorScript(): void
    {
        $scriptPath = $this->assets->path('assets/' . self::EDITOR_SCRIPT_FILE);

        if (!is_readable($scriptPath)) {
            return;
        }

        $metadata = $this->scriptMetadata(self::EDITOR_SCRIPT_FILE);

        wp_register_script(
            self::EDITOR_SCRIPT,
            $this->assets->url('assets/' . self::EDITOR_SCRIPT_FILE),
            $metadata['dependencies'],
            $metadata['version'],
            ['in_footer' => true],
        );
    }

    private function blockPath(): string
    {
        return dirname(__DIR__, 3) . '/resources/blocks/notes';
    }

    /** @return array{dependencies: list<string>, version: string} */
    private function scriptMetadata(string $file): array
    {
        $metadata = [
            'dependencies' => [
                'wp-block-editor',
                'wp-blocks',
                'wp-components',
                'wp-element',
                'wp-i18n',
                'wp-server-side-render',
            ],
            'version'      => $this->assets->version('assets/' . $file),
        ];

        $assetFileName = preg_replace('/\.js$/', '.asset.php', $file) ?: $file;
        $assetFile = $this->assets->path('assets/' . $assetFileName);

        if (!is_readable($assetFile)) {
            return $metadata;
        }

        // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
        $data = require $assetFile;

        if (!is_array($data)) {
            return $metadata;
        }

        $dependencies = $data['dependencies'] ?? [];
        $version = $data['version'] ?? null;

        if (is_array($dependencies)) {
            $metadata['dependencies'] = array_values(array_unique(array_filter(
                array_map(
                    static fn (mixed $dependency): string => is_scalar($dependency) ? (string) $dependency : '',
                    $dependencies,
                ),
            )));
        }

        if (is_scalar($version) || $version instanceof \Stringable) {
            $metadata['version'] = (string) $version;
        }

        return $metadata;
    }
}
