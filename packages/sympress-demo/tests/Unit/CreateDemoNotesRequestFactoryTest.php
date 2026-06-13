<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymPress\Demo\Application\Seed\CreateDemoNotesRequestFactory;
use SymPress\Demo\Support\SlugNormalizer;

final class CreateDemoNotesRequestFactoryTest extends TestCase
{
    public function testWpCliArgumentsAreNormalizedIntoASeedRequest(): void
    {
        $factory = new CreateDemoNotesRequestFactory(new SlugNormalizer());

        $request = $factory->fromWpCliArgs([
            'count' => '500',
            'set' => 'Frontend',
            'source' => 'Local',
            'topic' => 'Design Systems',
            'reset' => true,
        ]);

        self::assertSame(50, $request->count);
        self::assertSame('frontend', $request->setSlug);
        self::assertSame('local', $request->sourceSlug);
        self::assertSame('design-systems', $request->topicOverride);
        self::assertTrue($request->reset);
    }
}
