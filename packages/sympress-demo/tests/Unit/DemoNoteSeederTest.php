<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymPress\Demo\Application\Seed\CreateDemoNotesRequest;
use SymPress\Demo\Application\Seed\DemoNoteDraftFactory;
use SymPress\Demo\Application\Seed\DemoNoteFixtureProvider;
use SymPress\Demo\Application\Seed\DemoNoteSeeder;
use SymPress\Demo\Application\Seed\LocalQuoteProvider;
use SymPress\Demo\Application\Seed\QuoteFixtureFactory;
use SymPress\Demo\Application\Seed\StaticDemoNoteFixtureProvider;
use SymPress\Demo\Infrastructure\WordPress\ZenQuotesQuoteProvider;
use SymPress\Demo\Tests\Fixtures\InMemoryDemoNoteWriter;

final class DemoNoteSeederTest extends TestCase
{
    public function testSeederCreatesDraftsThroughTheWriterPort(): void
    {
        $writer = new InMemoryDemoNoteWriter();
        $writer->deleted = 4;
        $seeder = new DemoNoteSeeder($this->fixtureProvider(), new DemoNoteDraftFactory(), $writer);

        $result = $seeder->seed(new CreateDemoNotesRequest(
            count: 3,
            setSlug: 'quotes',
            sourceSlug: 'local',
            reset: true,
        ));

        self::assertSame(3, $result->created);
        self::assertSame(4, $result->deleted);
        self::assertSame('quotes', $result->setSlug);
        self::assertCount(3, $writer->drafts);
        self::assertSame('quotes', $writer->drafts[0]->topicSlug);
        self::assertSame('Quote by Oscar Wilde 01', $writer->drafts[0]->title);
        self::assertStringContainsString('Fixture context:', $writer->drafts[0]->content);
    }

    public function testSeederFallsBackWhenRemoteQuotesAreUnavailable(): void
    {
        $writer = new InMemoryDemoNoteWriter();
        $seeder = new DemoNoteSeeder($this->fixtureProvider(), new DemoNoteDraftFactory(), $writer);

        $result = $seeder->seed(new CreateDemoNotesRequest(
            count: 1,
            setSlug: 'quotes',
            sourceSlug: 'zenquotes',
        ));

        self::assertSame(1, $result->created);
        self::assertSame(['ZenQuotes could not be reached. Falling back to local quote examples.'], $result->warnings);
        self::assertSame('Quote by Oscar Wilde 01', $writer->drafts[0]->title);
    }

    private function fixtureProvider(): DemoNoteFixtureProvider
    {
        $quotes = new QuoteFixtureFactory();

        return new DemoNoteFixtureProvider(
            new StaticDemoNoteFixtureProvider(),
            new LocalQuoteProvider($quotes),
            new ZenQuotesQuoteProvider($quotes),
        );
    }
}
