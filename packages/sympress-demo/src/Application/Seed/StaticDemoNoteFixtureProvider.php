<?php

declare(strict_types=1);

namespace SymPress\Demo\Application\Seed;

final readonly class StaticDemoNoteFixtureProvider
{
    /** @return non-empty-list<DemoNoteFixture> */
    public function fixtures(string $setSlug): array
    {
        $sets = $this->sets();

        if ($setSlug === 'all') {
            return array_values(array_merge(...array_values($sets)));
        }

        return $sets[$setSlug] ?? $sets['architecture'];
    }

    public function hasSet(string $setSlug): bool
    {
        return $setSlug === 'all' || array_key_exists($setSlug, $this->sets());
    }

    /** @return array<string, non-empty-list<DemoNoteFixture>> */
    private function sets(): array
    {
        return [
            'architecture' => $this->architectureFixtures(),
            'frontend'     => $this->frontendFixtures(),
            'operations'   => $this->operationsFixtures(),
        ];
    }

    /** @return non-empty-list<DemoNoteFixture> */
    private function architectureFixtures(): array
    {
        return [
            new DemoNoteFixture(
                'architecture',
                'Thin Hook Boundary',
                'A note showing how WordPress hooks delegate to application services.',
                [
                    'The registrar parses WordPress input and immediately hands control to a service.',
                    'That boundary keeps hook timing visible without hiding business rules in callbacks.',
                ],
            ),
            new DemoNoteFixture(
                'architecture',
                'Repository Contract',
                'A repository interface keeps WordPress queries behind an application port.',
                [
                    'The application service depends on NoteRepositoryInterface rather than WP_Query.',
                    'Tests can use an in-memory repository while production still reads real posts.',
                ],
            ),
            new DemoNoteFixture(
                'events',
                'Rendered Note Event',
                'Events make side effects observable without coupling them to rendering.',
                [
                    'Optional render telemetry can dispatch NoteRenderedEvent with a small context payload.',
                    'The logging subscriber can change independently from the REST route or block.',
                ],
            ),
        ];
    }

    /** @return non-empty-list<DemoNoteFixture> */
    private function frontendFixtures(): array
    {
        return [
            new DemoNoteFixture(
                'frontend',
                'Encore Entrypoint',
                'Encore builds the admin, block editor and frontend entrypoints.',
                [
                    'TypeScript and SCSS live beside the plugin, but compiled assets are registered by WordPress.',
                    'The dependency extraction metadata keeps WordPress packages external in the editor.',
                ],
            ),
            new DemoNoteFixture(
                'frontend',
                'Dynamic Block Preview',
                'The editor block previews the same PHP render callback used on the frontend.',
                [
                    'ServerSideRender makes the editor example honest about WordPress as the runtime.',
                    'Changing limit or topic in the inspector exercises the same service layer.',
                ],
            ),
            new DemoNoteFixture(
                'frontend',
                'Reusable Note Template',
                'The block and REST-facing service share one view template for predictable output.',
                [
                    'Shared templates make the demo easier to inspect and reduce duplicate markup.',
                    'The rendered HTML remains ordinary WordPress-friendly markup.',
                ],
            ),
        ];
    }

    /** @return non-empty-list<DemoNoteFixture> */
    private function operationsFixtures(): array
    {
        return [
            new DemoNoteFixture(
                'operations',
                'Migration Friendly Table',
                'The demo event table is modeled as a versioned schema change.',
                [
                    'Structured projects need a repeatable way to introduce custom tables.',
                    'The migration class documents the table shape outside a random activation callback.',
                ],
            ),
            new DemoNoteFixture(
                'operations',
                'Profiler Collector',
                'The custom collector exposes demo runtime metrics in development.',
                [
                    'Profiler data turns architecture into something developers can inspect during a request.',
                    'The collector key connects the service tag to the rendered profiler panel.',
                ],
            ),
            new DemoNoteFixture(
                'testing',
                'Runtime Smoke Test',
                'Quality checks cover both package code and WordPress registrations.',
                [
                    'Unit tests prove the service behavior without booting WordPress.',
                    'The runtime smoke command confirms REST route and block registration in DDEV.',
                ],
            ),
        ];
    }
}
