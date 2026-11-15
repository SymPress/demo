<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use SymPress\Demo\Repository\WordPressNoteRepository;

final class WordPressNoteRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testPublicListingExcludesPasswordProtectedNotes(): void
    {
        $queries = [];
        Functions\when('get_posts')->alias(static function (array $args) use (&$queries): array {
            $queries[] = $args;

            return [];
        });

        (new WordPressNoteRepository())->findLatest(5);
        (new WordPressNoteRepository())->findByTopic('architecture', 5);

        self::assertCount(2, $queries);

        foreach ($queries as $args) {
            self::assertFalse($args['has_password'] ?? null);
        }
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testProtectedContentNeverReachesTheExcerptFallback(): void
    {
        Functions\when('get_posts')->justReturn([
            (object) ['ID' => 1, 'post_content' => 'Secret protected words', 'post_date_gmt' => '2026-11-15 10:00:00'],
            (object) ['ID' => 2, 'post_content' => '<p>Public note body</p>', 'post_date_gmt' => '2026-11-15 10:00:00'],
        ]);
        Functions\when('post_password_required')->alias(static fn (int $postId): bool => $postId === 1);

        $notes = (new WordPressNoteRepository())->findLatest(5);

        self::assertCount(2, $notes);
        self::assertSame('', $notes[0]->excerpt);
        self::assertSame('Public note body', $notes[1]->excerpt);
    }
}
