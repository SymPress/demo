<?php

declare(strict_types=1);

$bootstrapPaths = [
    __DIR__ . '/../../vendor/szepeviktor/phpstan-wordpress/bootstrap.php',
    __DIR__ . '/vendor/szepeviktor/phpstan-wordpress/bootstrap.php',
];

foreach ($bootstrapPaths as $bootstrapPath) {
    if (file_exists($bootstrapPath)) {
        require_once $bootstrapPath;

        return;
    }
}
