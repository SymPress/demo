<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class DemoNoteFixtureProvider
{
    public function __construct(
        private StaticDemoNoteFixtureProvider $staticFixtures,
        private LocalQuoteProvider $localQuotes,
        private RemoteQuoteProviderInterface $remoteQuotes,
    ) {
    }

    public function forRequest(CreateDemoNotesRequest $request): DemoNoteFixtureSelection
    {
        if ($request->setSlug === 'quotes') {
            return $this->quotes($request);
        }

        $warnings = [];
        $setSlug = $request->setSlug;

        if (!$this->staticFixtures->hasSet($setSlug)) {
            $warnings[] = sprintf('Unknown fixture set "%s". Falling back to "architecture".', $setSlug);
            $setSlug = 'architecture';
        }

        return new DemoNoteFixtureSelection(
            fixtures: $this->staticFixtures->fixtures($setSlug),
            setSlug: $setSlug,
            warnings: $warnings,
        );
    }

    private function quotes(CreateDemoNotesRequest $request): DemoNoteFixtureSelection
    {
        if ($request->sourceSlug === 'local') {
            return new DemoNoteFixtureSelection(
                fixtures: $this->localQuotes->quotes($request->count),
                setSlug: 'quotes',
            );
        }

        if ($request->sourceSlug !== 'zenquotes') {
            return new DemoNoteFixtureSelection(
                fixtures: $this->localQuotes->quotes($request->count),
                setSlug: 'quotes',
                warnings: [
                    sprintf('Unknown quote source "%s". Falling back to local quote examples.', $request->sourceSlug),
                ],
            );
        }

        $quotes = $this->remoteQuotes->quotes($request->count);

        if ($quotes !== []) {
            return new DemoNoteFixtureSelection(
                fixtures: $quotes,
                setSlug: 'quotes',
            );
        }

        return new DemoNoteFixtureSelection(
            fixtures: $this->localQuotes->quotes($request->count),
            setSlug: 'quotes',
            warnings: ['ZenQuotes could not be reached. Falling back to local quote examples.'],
        );
    }
}
