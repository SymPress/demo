# SymPress Components

This demo is designed to show every public SymPress package that belongs in a website-level reference project.

## Component Map

| Package | Role in the demo | Start reading |
|---|---|---|
| `sympress/kernel` | Boots the site kernel, discovers bundles and builds the service container. | `packages/base-mu-plugins/app-starter.php`, `packages/sympress-demo/config/services.yaml` |
| `sympress/event-dispatcher` | Demonstrates optional event telemetry and subscriber registration without making read queries write data. | `src/Application/Telemetry/NoteRenderTelemetry.php`, `src/Event/NoteRenderedEvent.php`, `src/EventSubscriber/LogRenderedNoteSubscriber.php` |
| `sympress/migration` | Models database changes as versioned migration classes. | `src/Migration/CreateDemoEventsTableMigration.php`, `src/Hook/DemoMigrations.php` |
| `sympress/assets` | Registers Encore-built frontend and admin assets through the AssetManager. | `src/Asset/DemoAssetRegistrar.php`, `assets/entrypoints.json` |
| `sympress/asset-compiler` | Discovers package asset metadata and runs the frontend build from Composer. | `composer.json`, `packages/sympress-demo/composer.json` |
| `sympress/wp-cli-console` | Shows CLI workflows as part of the application surface. | `src/Command/CreateDemoNotesCommand.php`, `src/Application/Seed/DemoNoteSeeder.php` |
| `sympress/monolog-bundle` | Provides PSR-3/Monolog-style logging configuration. | `config/packages/monolog.yaml`, `src/EventSubscriber/LogRenderedNoteSubscriber.php` |
| `sympress/orm` | Maps the demo event table as an entity and repository while keeping WordPress `wpdb` as runtime. | `src/Entity/DemoEventRecord.php`, `src/Repository/DemoEventRecordRepository.php` |
| `sympress/profiler` | Adds development-time runtime inspection through the web debug toolbar, profile pages and built-in collectors. | `config/packages/development/profiler.yaml`, `src/Profiler/DemoProfilerCollector.php` |
| `sympress/coding-standards` | Keeps the packages aligned with SymPress PHP quality conventions. | `composer qa`, `packages/sympress-demo/phpcs.xml.dist`, `packages/base-mu-plugins/phpcs.xml.dist` |
| `sympress/starter` | Provides the project-shape conventions mirrored by the demo website. | `bin/console`, `dev-ops/wpstarter.json`, `packages/base-mu-plugins` |

## How The Packages Work Together

The packages are intentionally used together rather than listed as passive dependencies.

`sympress/kernel` provides the container and hook compiler. The must-use app starter boots the site `SiteKernel`, then the plugin package contributes service configuration. Hook adapters such as `BlockRegistrar`, `TaxonomyRegistrar` and `DemoAssetRegistrar` are registered with `#[AsHook]` attributes, so WordPress hook placement stays next to the method that WordPress will call.

`sympress/event-dispatcher` gives the note workflow an extension point without making reads unsafe. `NoteService` only returns notes. `NoteRenderTelemetry` is an explicit, disabled-by-default side-effect service that can dispatch `NoteRenderedEvent` through the PSR dispatcher. `LogRenderedNoteSubscriber` registers itself during `event_dispatcher_register` and marks its handler with `#[AsEventListener]`, so another project could add analytics, cache warming or notifications without changing the note query service.

`sympress/migration` is represented by `CreateDemoEventsTableMigration` and `DemoMigrations`. The hook class registers the migration under the `sympress-demo` plugin slug and runs pending migrations after registration, the same pattern used by production packages in the reference project.

`sympress/assets` connects the Encore build with WordPress. Encore writes `entrypoints.json` and WordPress dependency extraction metadata for the admin, frontend and block editor entrypoints. `DemoAssetRegistrar` loads those files and registers the result with the AssetManager, matching the production package pattern in the reference project.

`sympress/asset-compiler` owns asset compilation at the website root. The root Composer package allows the plugin, enables auto-run and selects npm as the package-manager preference. The demo plugin declares its package-level build contract in Composer metadata, including build scripts and source paths. `composer compile-assets` now comes from the compiler package, and `composer build:production` runs that command in production mode before the runtime smoke command.

`sympress/wp-cli-console` is represented by a real seed command. The command delegates to `DemoNoteSeeder`; the default `quotes` set imports quote notes from a free API during seeding, with a local fallback, without putting fixture logic into activation hooks, templates or the CLI adapter itself.

`sympress/monolog-bundle` makes logging a configured project concern. The demo subscriber receives a PSR-3 logger, uses `#[WithMonologChannel('sympress_demo')]`, and writes useful runtime information without depending on a concrete logger implementation.

`sympress/orm` maps `sympress_demo_events` as `DemoEventRecord`. `DemoEventRepository` is the application-facing persistence adapter: it records through `DemoEventRecordRepository` and falls back to direct `wpdb` only while migrations catch up. The site imports the ORM service configuration explicitly from `vendor/sympress/orm/Resources/config/services.yaml` because the current ORM package keeps its service file under `Resources/config`.

`sympress/profiler` contributes the toolbar, profile pages and default request/runtime collectors in development installs when the active bundle is discovered by the kernel. The demo enables collection in development config and extends the profiler with `DemoProfilerCollector`, which records demo-specific counts and exposes them in the toolbar/profile UI. This separates the out-of-the-box profiler behavior from the application-specific extension example.

`sympress/coding-standards` is part of the quality workflow. The feature plugin and the base MU package both expose Composer scripts, so developers can check application code and bootstrap/runtime files separately. The point is not only formatting; it teaches contributors what kind of PHP shape SymPress packages expect.

## Runtime Versus Development Packages

Most SymPress packages are runtime dependencies because the website actively uses them while serving requests.

`sympress/coding-standards` and `sympress/profiler` are development dependencies. The profiler is demonstrated through development-only configuration, so local requests are inspectable without making profiling a production concern.

`sympress/starter` is represented as a project convention, not as a runtime dependency. The demo keeps the same shape a starter project teaches: `bin/console` as the command surface, WPStarter orchestration in `dev-ops/`, DDEV setup and a base MU package that boots the site kernel.

## What To Copy Into A Real Project

Copy the patterns, not every class name:

- keep the plugin file thin;
- put WordPress APIs at the infrastructure boundary;
- model application work as services;
- keep queries read-only, and put optional side effects behind explicit services;
- register assets from build metadata;
- put CLI workflows in commands;
- let REST routes and blocks delegate to the same services;
- keep translation loading WordPress-native but explicit;
- rely on package defaults where they exist, and add custom profiler collectors only for application-specific runtime insight;
- keep Composer dependencies Packagist-based and reserve path repositories for local packages developed inside the same workspace.

## What To Avoid

Do not copy the demo as a rigid framework template. A small plugin may not need every layer. SymPress is useful when the project has enough behavior that explicit boundaries reduce confusion.

A good rule: introduce a boundary when it removes a real coupling, makes testing easier or gives developers better runtime insight.
