<?php

declare(strict_types=1);

namespace SymPress\Demo\Profiler;

use SymPress\Demo\Entity\Note;
use SymPress\Demo\Entity\Topic;
use SymPress\Demo\Infrastructure\Persistence\DemoEventRepository;
use SymPress\Demo\Infrastructure\WordPress\BlockRegistrar;
use SymPress\Profiler\Collector\AbstractCollector;
use SymPress\Profiler\Collector\CollectorPanel;
use SymPress\Profiler\Contract\DataCollectorInterface;
use SymPress\Profiler\Support\Html;
use SymPress\Profiler\Value\ProfileContext;
use SymPress\Profiler\Value\ProfileRecord;
use SymPress\Profiler\Value\ToolbarBlock;

final class DemoProfilerCollector extends AbstractCollector implements DataCollectorInterface
{
    public function __construct(
        private readonly DemoEventRepository $events,
    ) {
    }

    public function getKey(): string
    {
        return 'sympress_demo';
    }

    public function getLabel(): string
    {
        return 'SymPress Demo';
    }

    public function getIcon(): string
    {
        return 'wordpress';
    }

    /**
     * @return array<string, mixed>
     */
    public function collect(ProfileContext $context): array
    {
        return [
            'notes' => $this->publishedNotes(),
            'topics' => $this->topics(),
            'events' => $this->events->count(),
            'post_type' => Note::POST_TYPE,
            'taxonomy' => Topic::TAXONOMY,
            'block' => BlockRegistrar::BLOCK_NAME,
            'block_registered' => $this->blockRegistered(),
            'collector_key' => $this->getKey(),
            'duration_ms' => $context->durationMs(),
            'captured_at' => $context->finishedAtIso(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createToolbarBlock(array $payload, ProfileRecord $profile): ToolbarBlock
    {
        return new ToolbarBlock(
            $this->getKey(),
            $this->getLabel(),
            sprintf('%d notes', $this->intValue($payload, 'notes')),
            sprintf(
                '%d topics · %d events',
                $this->intValue($payload, 'topics'),
                $this->intValue($payload, 'events'),
            ),
            $this->profileUrl($profile, $this->getKey()),
            'green',
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function renderPanel(array $payload, ProfileRecord $profile): CollectorPanel
    {
        unset($profile);

        $html = '<h2>SymPress Demo Application</h2>';
        $html .= Html::metricTiles([
            ['label' => 'Published notes', 'value' => (string) $this->intValue($payload, 'notes')],
            ['label' => 'Topics', 'value' => (string) $this->intValue($payload, 'topics')],
            ['label' => 'Demo events', 'value' => (string) $this->intValue($payload, 'events')],
            ['label' => 'Request duration', 'value' => sprintf('%.2f ms', $this->floatValue($payload, 'duration_ms'))],
        ]);
        $html .= Html::section('WordPress Surface', Html::keyValueTable([
            'post_type' => Html::dumpValue($this->stringValue($payload, 'post_type')),
            'taxonomy' => Html::dumpValue($this->stringValue($payload, 'taxonomy')),
            'block' => Html::dumpValue($this->stringValue($payload, 'block')),
            'block_registered' => Html::dumpValue($this->boolValue($payload, 'block_registered')),
        ]));
        $html .= Html::section('Profiler Extension Point', Html::keyValueTable([
            'collector_service' => Html::dumpValue(self::class),
            'collector_key' => Html::dumpValue($this->stringValue($payload, 'collector_key')),
            'service_tag' => Html::dumpValue('profiler.collector'),
            'captured_at' => Html::dumpValue($this->stringValue($payload, 'captured_at')),
        ]));

        return $this->panel(
            $this->getKey(),
            $this->getLabel(),
            $this->getIcon(),
            $html,
            (string) $this->intValue($payload, 'notes'),
        );
    }

    private function publishedNotes(): int
    {
        if (!function_exists('wp_count_posts')) {
            return 0;
        }

        $counts = wp_count_posts(Note::POST_TYPE);

        return is_object($counts) && is_numeric($counts->publish ?? null)
            ? (int) $counts->publish
            : 0;
    }

    private function topics(): int
    {
        if (!function_exists('wp_count_terms')) {
            return 0;
        }

        $count = wp_count_terms([
            'taxonomy' => Topic::TAXONOMY,
            'hide_empty' => false,
        ]);

        return is_numeric($count) ? (int) $count : 0;
    }

    private function blockRegistered(): bool
    {
        if (!class_exists(\WP_Block_Type_Registry::class)) {
            return false;
        }

        return \WP_Block_Type_Registry::get_instance()->is_registered(BlockRegistrar::BLOCK_NAME);
    }
}
