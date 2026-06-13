<?php

declare(strict_types=1);

$autoloadCandidates = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 3) . '/vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoload) {
    if (is_readable($autoload)) {
        require $autoload;
        require __DIR__ . '/Fixtures/InMemoryNoteRepository.php';
        require __DIR__ . '/Fixtures/RecordingEventDispatcher.php';

        return;
    }
}

fwrite(STDERR, "Install Composer dependencies before running the test suite.\n");
exit(1);
