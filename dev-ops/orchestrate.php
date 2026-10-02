<?php

declare(strict_types=1);

use SymPress\Runtime\Database\DbChecker;
use SymPress\Runtime\Services;

$config = (object) [
    'title' => 'SymPress Demo',
];

$shellArg = static fn (string $value): string => \escapeshellarg($value);
$homepageBlock = '<!-- wp:sympress-demo/notes {"limit":6} /-->';

$runtimeCommands = static function (string $homepageBlock): array {
    $encodedHomepageBlock = base64_encode($homepageBlock);

    return [
        'wp plugin activate sympress-demo',
        'wp rewrite flush',
        'wp sympress-demo:create-notes --set=quotes --count=18 --reset',
        "wp eval '\$content = base64_decode(\"{$encodedHomepageBlock}\"); \$page = get_page_by_path(\"sympress-demo-home\"); if (\$page) { wp_update_post([\"ID\" => \$page->ID, \"post_content\" => \$content, \"post_status\" => \"publish\"]); } else { \$pageId = wp_insert_post([\"post_type\" => \"page\", \"post_title\" => \"SymPress Demo\", \"post_name\" => \"sympress-demo-home\", \"post_content\" => \$content, \"post_status\" => \"publish\"]); \$page = is_wp_error(\$pageId) ? null : get_post((int) \$pageId); } if (\$page) { update_option(\"show_on_front\", \"page\"); update_option(\"page_on_front\", \$page->ID); }'",
    ];
};

/** @var Services $services */
$env = $services->env();

if (!$env->read(DbChecker::WPDB_ENV_VALID)) {
    return ['wp --version'];
}

if ($env->read(DbChecker::WP_INSTALLED)) {
    return ['wp db check'];
}

$commands = [];

if (!$env->read(DbChecker::WPDB_EXISTS)) {
    $commands[] = 'wp db create';
}

$user = $env->read('WP_ADMIN_USERNAME') ?: 'admin';
$pass = $env->read('WP_ADMIN_PASSWORD');

if (!$pass || $pass === 'admin') {
    $pass = \bin2hex(\random_bytes(24));
}

$home = $env->read('WP_HOME');
$siteUrl = $env->read('WP_SITEURL') ?: $home;
$email = $env->read('WP_ADMIN_EMAIL') ?: 'admin@example.invalid';

$install = 'wp core install';
$install .= ' --skip-packages --skip-email';
$install .= ' --title=' . $shellArg($config->title) . ' --url=' . $shellArg((string) $home);
$install .= ' --admin_user=' . $shellArg((string) $user);
$install .= ' --admin_password=' . $shellArg((string) $pass);
$install .= ' --admin_email=' . $shellArg($email);

$commands[] = $install;
$commands[] = 'wp option update siteurl ' . $shellArg((string) $siteUrl);
$commands[] = 'wp rewrite flush';
$commands[] = 'wp theme activate twentytwentyfive';
$commands = [
    ...$commands,
    ...$runtimeCommands($homepageBlock),
];

return $commands;
