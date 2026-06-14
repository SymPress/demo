<?php

declare(strict_types=1);

/** @var array{noteCount: int, topicCount: int, eventCount: int, latestEvents: list<array{name: string, createdAt: string, context: string}>, components: list<array{name: string, package: string, repository: string, status: string}>, serviceContainer: list<array{contract: string, service: string, pattern: string}>, starter: list<array{name: string, package: string, path: string, status: string}>, profiler: array{collector: class-string, key: string, tag: string, status: string}, sourceLinks: list<array{label: string, description: string, path: string, url: string}>} $data */

?>
<div class="wrap sympress-demo-admin">
    <h1><?php echo esc_html__('SymPress Demo', 'sympress-demo'); ?></h1>
    <p class="sympress-demo-admin__intro">
        <?php echo esc_html__('A reference WordPress plugin demonstrating structured development with SymPress and Symfony components.', 'sympress-demo'); ?>
    </p>

    <div class="sympress-demo-admin__stats">
        <section>
            <strong><?php echo esc_html((string) $data['noteCount']); ?></strong>
            <span><?php echo esc_html__('Published notes', 'sympress-demo'); ?></span>
        </section>
        <section>
            <strong><?php echo esc_html((string) $data['topicCount']); ?></strong>
            <span><?php echo esc_html__('Topics', 'sympress-demo'); ?></span>
        </section>
        <section>
            <strong><?php echo esc_html((string) $data['eventCount']); ?></strong>
            <span><?php echo esc_html__('Recorded demo events', 'sympress-demo'); ?></span>
        </section>
    </div>

    <div class="sympress-demo-admin__service-container">
        <h2><?php echo esc_html__('Service container', 'sympress-demo'); ?></h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Contract', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Resolved service', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Pattern', 'sympress-demo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['serviceContainer'] as $service): ?>
                    <tr>
                        <td><code><?php echo esc_html($service['contract']); ?></code></td>
                        <td><code><?php echo esc_html($service['service']); ?></code></td>
                        <td><?php echo esc_html($service['pattern']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="sympress-demo-admin__starter">
        <h2><?php echo esc_html__('Starter project conventions', 'sympress-demo'); ?></h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Convention', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Package', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Path', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Status', 'sympress-demo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['starter'] as $convention): ?>
                    <tr>
                        <td><?php echo esc_html($convention['name']); ?></td>
                        <td><code><?php echo esc_html($convention['package']); ?></code></td>
                        <td><code><?php echo esc_html($convention['path']); ?></code></td>
                        <td>
                            <span
                                class="sympress-demo-admin__status sympress-demo-admin__status--<?php echo esc_attr($convention['status']); ?>"
                                data-component-status="<?php echo esc_attr($convention['status']); ?>"
                            >
                                <?php echo esc_html($convention['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2><?php echo esc_html__('SymPress components', 'sympress-demo'); ?></h2>
    <table class="widefat striped sympress-demo-admin__components">
        <thead>
            <tr>
                <th><?php echo esc_html__('Component', 'sympress-demo'); ?></th>
                <th><?php echo esc_html__('Package', 'sympress-demo'); ?></th>
                <th><?php echo esc_html__('Status', 'sympress-demo'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['components'] as $component): ?>
                <tr>
                    <td><?php echo esc_html($component['name']); ?></td>
                    <td>
                        <a href="<?php echo esc_url($component['repository']); ?>" target="_blank" rel="noreferrer">
                            <?php echo esc_html($component['package']); ?>
                        </a>
                    </td>
                    <td>
                        <span
                            class="sympress-demo-admin__status sympress-demo-admin__status--<?php echo esc_attr($component['status']); ?>"
                            data-component-status="<?php echo esc_attr($component['status']); ?>"
                        >
                            <?php echo esc_html($component['status']); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="sympress-demo-admin__events">
        <h2><?php echo esc_html__('Latest ORM event records', 'sympress-demo'); ?></h2>
        <?php if ($data['latestEvents'] === []): ?>
            <p><?php echo esc_html__('No demo events have been recorded yet.', 'sympress-demo'); ?></p>
        <?php else: ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Event', 'sympress-demo'); ?></th>
                        <th><?php echo esc_html__('Created', 'sympress-demo'); ?></th>
                        <th><?php echo esc_html__('Context keys', 'sympress-demo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['latestEvents'] as $event): ?>
                        <tr>
                            <td><code><?php echo esc_html($event['name']); ?></code></td>
                            <td><?php echo esc_html($event['createdAt']); ?></td>
                            <td><?php echo esc_html($event['context']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="sympress-demo-admin__usage">
        <h2><?php echo esc_html__('Developer entry points', 'sympress-demo'); ?></h2>
        <p><code>sympress-demo/notes</code></p>
        <p><code>&lt;!-- wp:sympress-demo/notes {"limit":5,"topic":"quotes"} /--&gt;</code></p>
        <p><code>/wp-json/sympress-demo/v1/notes?limit=5&amp;topic=architecture</code></p>
        <p><code>wp sympress-demo:create-notes --set=quotes --count=10</code></p>
    </div>

    <div class="sympress-demo-admin__source">
        <h2><?php echo esc_html__('Source code map', 'sympress-demo'); ?></h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Entry', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Runtime surface', 'sympress-demo'); ?></th>
                    <th><?php echo esc_html__('Source', 'sympress-demo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['sourceLinks'] as $source): ?>
                    <tr>
                        <td><?php echo esc_html($source['label']); ?></td>
                        <td><code><?php echo esc_html($source['description']); ?></code></td>
                        <td>
                            <a href="<?php echo esc_url($source['url']); ?>" target="_blank" rel="noreferrer">
                                <?php echo esc_html($source['path']); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="sympress-demo-admin__profiler">
        <h2><?php echo esc_html__('Profiler extension point', 'sympress-demo'); ?></h2>
        <p>
            <span class="sympress-demo-admin__status sympress-demo-admin__status--<?php echo esc_attr($data['profiler']['status']); ?>">
                <?php echo esc_html($data['profiler']['status']); ?>
            </span>
        </p>
        <div class="sympress-demo-admin__code-list">
            <code><?php echo esc_html($data['profiler']['collector']); ?></code>
            <code><?php echo esc_html('packages/sympress-demo/config/services.yaml'); ?></code>
            <code><?php echo esc_html($data['profiler']['tag']); ?></code>
            <code><?php echo esc_html($data['profiler']['key']); ?></code>
            <code><?php echo esc_html('/_profiler/{token}#panel-' . $data['profiler']['key']); ?></code>
        </div>
    </div>
</div>
