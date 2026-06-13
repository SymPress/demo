<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class DemoNoteSeeder
{
    public function __construct(
        private DemoNoteFixtureProvider $fixtures,
        private DemoNoteDraftFactory $drafts,
        private DemoNoteWriterInterface $writer,
    ) {
    }

    public function seed(CreateDemoNotesRequest $request): CreateDemoNotesResult
    {
        $selection = $this->fixtures->forRequest($request);
        $deleted = $request->reset ? $this->writer->deleteAll() : 0;
        $created = 0;
        $warnings = $selection->warnings;

        for ($index = 1; $index <= $request->count; $index++) {
            $fixture = $selection->fixtures[($index - 1) % count($selection->fixtures)];
            $error = $this->writer->save($this->drafts->create(
                $fixture,
                $index,
                $selection->setSlug,
                $request->topicOverride,
            ));

            if ($error !== null) {
                $warnings[] = $error;

                continue;
            }

            $created++;
        }

        return new CreateDemoNotesResult(
            created: $created,
            deleted: $deleted,
            setSlug: $selection->setSlug,
            warnings: $warnings,
        );
    }
}
