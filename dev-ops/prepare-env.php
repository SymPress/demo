<?php

declare(strict_types=1);

// Prepare a private site key before Composer/WordPress boot; never print or rotate an existing key.
if (PHP_SAPI !== 'cli') {
    exit(1);
}
$path = dirname(__DIR__) . '/.env';
if (!is_file($path) || is_link($path)) {
    fwrite(STDERR, "Create a regular .env from .env.example before preparing its private key.\n");
    exit(1);
}
$stream = fopen($path, 'r+');
if ($stream === false || !flock($stream, LOCK_EX)) {
    throw new RuntimeException('Cannot lock the private environment file.');
}
try {
    if (!chmod($path, 0600)) {
        throw new RuntimeException('Cannot protect the private environment file.');
    }
    $contents = stream_get_contents($stream);
    if (!is_string($contents)) {
        throw new RuntimeException('Cannot read the private environment file.');
    }
    foreach (['APP_SECRET', 'APP_SECRET_FILE'] as $name) {
        $processSecret = getenv($name);
        if (is_string($processSecret) && $processSecret !== '') {
            exit(0);
        }
    }
    preg_match_all('/^\h*(?:export\h+)?APP_SECRET(?:_FILE)?\h*=([^\r\n]*)/m', $contents, $matches);
    foreach ($matches[1] as $value) {
        if (preg_match('/^(?:(?:""|\'\')\h*)?(?:#.*)?$/D', trim($value)) !== 1) {
            // Invalid nonempty values are diagnosed by FrameworkBundle, never replaced here.
            exit(0);
        }
    }
    $line = ($contents === '' || str_ends_with($contents, "\n") ? '' : "\n")
        . 'APP_SECRET=' . bin2hex(random_bytes(32)) . "\n";
    if (fseek($stream, 0, SEEK_END) !== 0 || fwrite($stream, $line) !== strlen($line) || !fflush($stream)) {
        throw new RuntimeException('Cannot persist the private site key.');
    }
} finally {
    flock($stream, LOCK_UN);
    fclose($stream);
}
