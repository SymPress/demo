# SymPress Demo Plugin

This package contains the WordPress plugin used by the SymPress Demo website.

The package is intentionally written as a small application package, not as a single procedural plugin file. WordPress is still the runtime, but most behavior is expressed as services, adapters, events and commands.

It demonstrates:

- Custom post type registration
- Taxonomy registration
- Thin WordPress hook adapters
- Query and seed use cases modeled as application services
- Service-oriented note retrieval
- Event dispatching and subscribers
- Versioned SymPress database migrations
- REST API route backed by the note service
- Dynamic block editor example
- WP-CLI seed command backed by a testable seeder
- Admin, frontend and block editor assets built with Encore and TypeScript
- WordPress textdomain loading with translation source files
- Monolog-compatible logging
- Demo-specific SymPress Profiler collector extension

## Package Idea

The plugin file is only the WordPress package entrypoint. It exposes plugin metadata and loads Composer autoloading when needed. The website itself boots the shared SymPress `SiteKernel` from `packages/base-mu-plugins/app-starter.php`, matching the real reference project shape.

The package becomes part of the SymPress runtime through Composer metadata:

```json
{
  "extra": {
    "kernel": {
      "bundle": "SymPress\\Demo\\SymPressDemoBundle",
      "entry": "sympress-demo/sympress-demo.php"
    }
  }
}
```

When WordPress marks the plugin active, the kernel can discover `SymPressDemoBundle` and load `config/services.yaml`.

The code is organized around boundaries:

- `Entity/` contains plain PHP note and topic entities.
- `Application/` contains query, telemetry and seeding use cases plus request/result objects.
- `Repository/` contains the note repository contract and WordPress-backed implementation.
- `Service/` contains application use-case services.
- `Presentation/` contains response/resource shaping for outward-facing adapters.
- `Infrastructure/WordPress/` contains adapters that call WordPress APIs.
- `Infrastructure/Persistence/` contains the demo event table persistence.
- `Hook/` contains package hook adapters such as migration registration.
- `Migration/` contains versioned schema changes.
- `Event/` and `EventSubscriber/` make side effects explicit.
- `Asset/` connects Encore build output with WordPress asset loading.
- `Profiler/` adds package-specific runtime metrics to the development-only SymPress profiler.

When reading the package, start with `Infrastructure/WordPress/BlockRegistrar.php`, then follow the call into `Application/Query/NoteListQueryFactory.php` and `Service/NoteService.php`. After that, compare `RestApiRegistrar.php` and `BlockRegistrar.php`: both entry points reuse the same query/service flow instead of duplicating application behavior.

## Service Tags

The package declares runtime integration points in `config/services.yaml`:

- `kernel.hook` for WordPress hooks and filters such as `init`, `admin_menu`, `rest_api_init`, `cli_init` and migration hooks.
- `profiler.collector` for the demo-specific profiler panel in `config/services_development.yaml`.
- An alias from `NoteRepositoryInterface` to `WordPressNoteRepository` so the application service depends on a contract.
- An alias from `DemoNoteWriterInterface` to `WordPressDemoNoteWriter` so the seed use case writes through a port.

Those tags are the package's public integration surface. The plugin bootstrap stays thin because the container owns the runtime wiring.

## Profiler Extension

`sympress/profiler` provides the toolbar, profile pages and default collectors in development once the profiler bundle is active and discovered. This package only demonstrates how an application can add a custom panel:

```text
SymPress\Demo\Profiler\DemoProfilerCollector
profiler.collector
```

The collector records demo-specific counts such as published notes, topics, demo events and block registration state. It should not be copied into the profiler package; real projects should add their own collectors only when they have project-specific runtime information to expose.

## Development

Run setup and quality checks from the repository root:

```bash
ddev composer install
ddev composer qa
```

The package-level Composer scripts exist for focused checks, but the root workflow also runs the WordPress runtime smoke test.

More context lives in the repository-level documentation:

- [Architecture](../../docs/architecture.md)
- [SymPress Components](../../docs/sympress-components.md)
- [Developer Walkthrough](../../docs/developer-walkthrough.md)
