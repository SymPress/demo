<?php

declare(strict_types=1);

/**
 * Plugin Name: SymPress Demo Allowed HTML Tags
 * Description: Extends the WordPress content sanitizer for demo media markup.
 */

namespace SymPress\Demo\BaseMuPlugins\Content;

if (!defined('ABSPATH')) {
    exit;
}

final class AllowedHtmlAttributesManager
{
    private const array ADDITIONAL_TAGS = [
        'source' => [
            'src'  => [],
            'type' => [],
        ],
    ];

    public static function initialize(): void
    {
        add_filter('wp_kses_allowed_html', [self::class, 'addAllowedTags'], 10, 2);
    }

    /**
     * @param array<string, array<string, array<mixed>>> $tags
     * @return array<string, array<string, array<mixed>>>
     */
    public static function addAllowedTags(array $tags, mixed $context): array
    {
        if ($context !== 'post') {
            return $tags;
        }

        return array_merge($tags, self::ADDITIONAL_TAGS);
    }
}

AllowedHtmlAttributesManager::initialize();
