# SymPress Demo

## Purpose and boundaries

This is the runnable reference website for the public SymPress packages. WordPress
owns the runtime; SymPress provides the kernel, container and package structure.
Keep WordPress API calls in adapters and application decisions in testable services.

## Read first

- `docs/architecture.md` — bootstrap, request lifecycle and layer boundaries.
- `docs/developer-walkthrough.md` — one feature traced end to end.
- `docs/sympress-components.md` — package-to-code map.
- `packages/sympress-demo/` — the feature package; `config/` is site-level config.

Do not edit generated WordPress files under `public/`. Sources for committed assets
live under `packages/sympress-demo/resources/`; rebuild assets instead of hand-editing
files under `packages/sympress-demo/assets/`.

## Verification

For PHP-only changes that do not need WordPress runtime state:

```sh
ddev composer cs
ddev composer static-analysis
ddev composer test
```

For TypeScript or asset changes:

```sh
ddev npm --prefix packages/sympress-demo run typecheck
ddev npm --prefix packages/sympress-demo run build:production
```

Run `ddev composer qa` for bootstrap, service configuration, hooks, REST, block,
migration or ORM changes. It includes the live WP-CLI runtime smoke and therefore
requires an installed DDEV site. Browser-facing changes also require the Playwright
workflow or the equivalent local browser test.

## Invariants

- `packages/base-mu-plugins/app-starter.php` is the website kernel bootstrap.
- The demo plugin file stays a thin WordPress metadata/autoload entry point.
- REST and block inputs share `NoteListQueryFactory`; do not fork normalization.
- Read queries stay read-only. Telemetry and other writes remain explicit opt-ins.
- Package services belong in package config; site overrides belong in root `config/`.
- Keep `composer.json` component metadata and `docs/sympress-components.md` aligned.

## Definition of done

Update documentation and committed build output when their source contract changes.
Report fast checks separately from DDEV/runtime/browser checks, including anything
not run. Do not commit `.env`, credentials, runtime caches or dependency directories.
