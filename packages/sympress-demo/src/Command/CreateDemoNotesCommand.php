<?php

declare(strict_types=1);

namespace SymPress\Demo\Command;

use SymPress\Demo\Application\Seed\CreateDemoNotesRequestFactory;
use SymPress\Demo\Application\Seed\DemoNoteSeeder;
use SymPress\Kernel\Attribute\AsHook;

/**
 * WP-CLI entry point for demo content.
 *
 * In projects using sympress/wp-cli-console, this command can be exposed through
 * the Symfony Console bridge instead of native WP_CLI registration.
 */
final readonly class CreateDemoNotesCommand
{
    public function __construct(
        private CreateDemoNotesRequestFactory $requests,
        private DemoNoteSeeder $seeder,
    ) {
    }

    #[AsHook('cli_init')]
    public function register(): void
    {
        if (!defined('WP_CLI') || !WP_CLI || !class_exists('WP_CLI')) {
            return;
        }

        \WP_CLI::add_command('sympress-demo:create-notes', $this);
    }

    /**
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of notes to create. Defaults to 5.
     *
     * [--topic=<topic>]
     * : Optional topic slug override. By default each fixture chooses its own topic.
     *
     * [--set=<set>]
     * : Fixture set to create: quotes, architecture, frontend, operations or all. Defaults to quotes.
     *
     * [--source=<source>]
     * : Quote source for --set=quotes: zenquotes or local. Defaults to zenquotes.
     *
     * [--reset]
     * : Delete existing demo notes before creating the new fixture set.
     *
     * ## EXAMPLES
     *
     *     wp sympress-demo:create-notes --set=quotes --count=10 --reset
     *     wp sympress-demo:create-notes --set=quotes --source=local --count=10
     *
     * @param list<string> $args
     * @param array<string, mixed> $assocArgs
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        unset($args);

        $result = $this->seeder->seed($this->requests->fromWpCliArgs($assocArgs));

        if ($result->deleted > 0) {
            \WP_CLI::line(sprintf('Deleted %d existing SymPress Demo notes.', $result->deleted));
        }

        foreach ($result->warnings as $warning) {
            \WP_CLI::warning($warning);
        }

        \WP_CLI::success(sprintf(
            'Created %d SymPress Demo notes from the "%s" fixture set.',
            $result->created,
            $result->setSlug,
        ));
    }
}
