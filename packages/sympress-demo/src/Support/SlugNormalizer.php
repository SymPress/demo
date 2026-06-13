<?php

declare(strict_types=1);

namespace SymPress\Demo\Support;

final readonly class SlugNormalizer
{
    public function normalize(mixed $value, string $default = ''): string
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return $default;
        }

        $slug = trim((string) $value);

        if ($slug === '') {
            return $default;
        }

        if (function_exists('sanitize_title')) {
            $normalized = sanitize_title($slug);

            return $normalized !== '' ? $normalized : $default;
        }

        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : $default;
    }
}
