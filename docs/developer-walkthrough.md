# Developer Walkthrough

This walkthrough follows one feature through the project: rendering demo notes on the homepage.

## 1. Start With The User-Facing Feature

The homepage contains the dynamic notes block:

```text
<!-- wp:sympress-demo/notes {"limit":6} /-->
```

The same application surface is also available through:

```text
/wp-json/sympress-demo/v1/notes
sympress-demo/notes
```

The REST route and dynamic block are intentionally two entry points into the same note service. A developer using the demo should first ask:

- Where does WordPress enter the package?
- Where is the application behavior?
- Where are side effects recorded?
- How can I inspect the runtime?

## 2. WordPress Enters Through A Thin Block Adapter

Before the block render callback runs, the website has already booted the shared `SiteKernel` from:

```text
packages/base-mu-plugins/app-starter.php
```

That is the same website-level bootstrap shape as the reference project. The regular plugin file stays thin and does not own the kernel.

Start in:

```text
packages/sympress-demo/src/Infrastructure/WordPress/BlockRegistrar.php
packages/sympress-demo/src/Application/Query/NoteListQueryFactory.php
```

The block registrar is intentionally small. Its job is to:

- register the dynamic block metadata;
- hand block attributes to the shared query factory;
- call the application service;
- render a view;
- avoid owning the actual note retrieval logic.

That keeps WordPress-specific concerns at the edge.

## 3. Application Logic Lives In A Service

Continue to:

```text
packages/sympress-demo/src/Service/NoteService.php
packages/sympress-demo/src/Repository/NoteRepositoryInterface.php
```

`NoteService` works with a `NoteRepositoryInterface`. It does not need to know whether notes come from WordPress posts, an external API or an in-memory test fixture.

This is the first important SymPress lesson: use WordPress as infrastructure, not as the place where every decision has to live.

## 4. WordPress Data Is Adapted Into Entities

Continue to:

```text
packages/sympress-demo/src/Repository/WordPressNoteRepository.php
```

The repository converts WordPress posts and terms into entity objects:

- `Entity/Note.php`
- `Entity/Topic.php`

That gives the rest of the package stable objects instead of making every class know about `WP_Post`, term arrays and global functions.

## 5. REST And Blocks Reuse The Same Service

Continue to:

```text
packages/sympress-demo/src/Infrastructure/WordPress/RestApiRegistrar.php
packages/sympress-demo/src/Infrastructure/WordPress/BlockRegistrar.php
packages/sympress-demo/src/Application/Query/NoteListQueryFactory.php
packages/sympress-demo/resources/ts/block-editor.ts
packages/sympress-demo/resources/blocks/notes/block.json
```

These adapters do not duplicate note retrieval or query normalization. They pass WordPress input through `NoteListQueryFactory`, call `NoteService` and return a WordPress-shaped response: JSON for REST, rendered HTML for the block.

This is the second important SymPress lesson: adding a new WordPress surface should not require rewriting the application behavior.

## 6. Side Effects Are Events

Continue to:

```text
packages/sympress-demo/src/Application/Telemetry/NoteRenderTelemetry.php
packages/sympress-demo/src/Event/NoteRenderedEvent.php
packages/sympress-demo/src/EventSubscriber/LogRenderedNoteSubscriber.php
```

The note query itself is read-only. If telemetry is explicitly enabled, `NoteRenderTelemetry` emits an event after a render surface has loaded notes. The subscriber reacts by logging and recording demo events.

The subscriber is registered through:

```text
packages/sympress-demo/config/services.yaml
packages/sympress-demo/src/EventSubscriber/LogRenderedNoteSubscriber.php
```

This matters because side effects often start small and then multiply. Events give them a named place to live without making the feature service or public REST query grow sideways.

## 7. Persistence Is Explicit

Continue to:

```text
packages/sympress-demo/src/Infrastructure/Persistence/DemoEventRepository.php
packages/sympress-demo/src/Migration/CreateDemoEventsTableMigration.php
packages/sympress-demo/src/Hook/DemoMigrations.php
```

The demo event table is visible as a project concern. The repository writes records; the migration owns the schema; the hook class registers and runs it through `sympress/migration`.

The idea is simple: when a plugin owns data outside standard posts and terms, that data should be understandable from code, not only from a database dump.

## 8. Assets Are Built, Then Registered

Continue to:

```text
packages/sympress-demo/resources/ts/frontend.ts
packages/sympress-demo/resources/ts/block-editor.ts
packages/sympress-demo/resources/scss/frontend.scss
packages/sympress-demo/webpack.config.js
packages/sympress-demo/src/Asset/DemoAssetRegistrar.php
```

The source assets live in `resources/`. Encore builds them into `assets/`. WordPress loads the built files.

At website level, Composer is the build entry point:

```bash
ddev composer compile-assets
```

The root Composer script installs the demo plugin frontend dependencies and runs its Encore build. The plugin package also declares its asset build inputs in Composer metadata, so the package contract is visible even though this demo currently uses an explicit root script.

This split teaches an important production pattern:

- source files are for developers;
- built files are for WordPress runtime;
- registration reads build metadata instead of hard-coding every generated detail.

## 9. Runtime Understanding Comes From The Profiler

The profiler package itself supplies the toolbar, profile pages and default collectors when `sympress/profiler` is installed, active and discovered by the kernel. Those built-in panels show request, performance, database, hook, asset, template, block, option and kernel information without a demo-specific collector.

Continue to:

```text
packages/sympress-demo/src/Profiler/DemoProfilerCollector.php
packages/sympress-demo/config/services_development.yaml
```

The demo collector is tagged as `profiler.collector` in `services_development.yaml` and adds package-specific metrics:

- published notes;
- topics;
- recorded demo events;
- block registration state;
- collector key and service tag.

A developer can open the profiler, inspect the built-in panels first, and then confirm that the application package is active through the optional `SymPress Demo` panel.

## 10. Developer Workflows Are Commands

Continue to:

```text
packages/sympress-demo/src/Command/CreateDemoNotesCommand.php
packages/sympress-demo/src/Application/Seed/DemoNoteSeeder.php
packages/sympress-demo/src/Infrastructure/WordPress/WordPressDemoNoteWriter.php
```

The command is only the WP-CLI adapter. It creates a seed request, calls `DemoNoteSeeder`, and prints the result. Fixture selection, quote fallbacks, draft creation and WordPress persistence each live behind smaller classes.

```bash
ddev exec 'vendor/bin/wp sympress-demo:create-notes --count=10 --topic=architecture'
ddev exec 'vendor/bin/wp sympress-demo:create-notes --set=quotes --count=18 --reset'
```

The workflow is deliberately not hidden in installation logic. Developers can run it repeatedly, inspect its behavior and adapt the pattern for real project fixtures without turning the command class into a second plugin bootstrap.

## 11. Localization Stays WordPress-Native

Continue to:

```text
packages/sympress-demo/src/Infrastructure/WordPress/LocalizationRegistrar.php
packages/sympress-demo/languages/sympress-demo.pot
packages/sympress-demo/languages/sympress-demo-de_DE.po
```

The demo loads a normal WordPress textdomain. SymPress does not replace the translation mechanism; it gives the loading code an explicit place in the package.

## 12. Quality Checks Guard The Shape

Run:

```bash
ddev composer qa
```

The QA command runs PHPCS, PHPStan, PHPUnit and a runtime smoke test for the demo package. The tests are intentionally close to the architecture:

- service behavior;
- event dispatching;
- bootstrap and metadata shape;
- live REST route, block registration and block rendering in WordPress.

The tests are not exhaustive. Their purpose is to show where confidence belongs first in a structured plugin.

## Reading Order For New Developers

Use this order when onboarding:

1. Read the root `README.md` to understand the project goal.
2. Open the homepage and admin dashboard.
3. Run the fixture command.
4. Follow the block path in this walkthrough.
5. Open the profiler and inspect the `SymPress Demo` panel.
6. Read `docs/sympress-components.md` to map packages to code.
7. Run `ddev composer qa`.

After that, the demo should feel less like a folder tree and more like a small application whose boundaries are visible.
