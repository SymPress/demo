<?php

declare(strict_types=1);

use SymPress\Demo\Entity\Note;

/** @var array{notes?: list<Note>, topic?: string, limit?: int} $viewData */
$notes = $viewData['notes'] ?? [];
$topic = $viewData['topic'] ?? '';
$limit = $viewData['limit'] ?? 0;

?>
<div class="sympress-demo-notes" data-limit="<?php echo esc_attr((string) $limit); ?>" data-topic="<?php echo esc_attr($topic); ?>">
    <?php if ($notes === []): ?>
        <p class="sympress-demo-notes__empty">
            <?php echo esc_html__('No knowledge notes found.', 'sympress-demo'); ?>
        </p>
    <?php else: ?>
        <ul class="sympress-demo-notes__list">
            <?php foreach ($notes as $note): ?>
                <li class="sympress-demo-notes__item">
                    <article>
                        <div class="sympress-demo-notes__meta">
                            <span><?php echo esc_html($note->topicName()); ?></span>
                            <time datetime="<?php echo esc_attr($note->publishedAt->format(DATE_ATOM)); ?>">
                                <?php echo esc_html($note->publishedAt->format('M j, Y')); ?>
                            </time>
                        </div>
                        <h3 class="sympress-demo-notes__title">
                            <a href="<?php echo esc_url($note->url); ?>">
                                <?php echo esc_html($note->title); ?>
                            </a>
                        </h3>
                        <p class="sympress-demo-notes__excerpt">
                            <?php echo esc_html($note->excerpt); ?>
                        </p>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
