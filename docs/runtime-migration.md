# Runtime migration review

The demo replaces WP Starter with `sympress/runtime` and `dev-ops/runtime.json`. Compatibility is disabled; the previous dotenv file convention is explicit. The command provider reads shared Runtime preflight status and keeps the existing installation and seed commands. Its path is now project-root-relative. The base MU package resolves the project autoloader for both copied and linked installation and boots the kernel only once.

The dependency uses `^1.0@RC` from public Packagist for release-candidate evaluation. The reviewed `composer.lock` pins `v1.0.0-rc.1` at `b532fdc06c3e46b1f7b10a99557814b283d5018d`; Runtime no longer needs a private Git repository or SSH authentication. WP-CLI uses Runtime's SHA512-verified root phar, preserving CLI capabilities without the Composer bundle's Symfony Process dependency conflict. Static analysis reads the actual phar command class. Generated `public/index.php` is no longer tracked; setup creates it.

Public WordPress URLs remain at the site root: `WP_SITEURL=${WP_HOME}`. The original DDEV Nginx rules map public endpoints to the physical `public/wp` core directory. No Nginx routing changes are required by this migration.

## Runtime 1.0.0-rc.1 upgrade verification

The Composer requirement is `^1.0@RC`; the reviewed lock pins RC 1 exactly.
This keeps normal Composer constraint validation enabled while making installs
reproducible. Only Runtime changes in the dependency graph. Its metadata now
comes from Packagist, with public HTTPS source and distribution URLs. The existing
WP-CLI download lock and all artifact hashes remain unchanged.

Verified on 2026-09-30 in the isolated `sympress-v1-demo` DDEV project with PHP
8.5.9, Composer 2.10.3 and the locked WordPress 7.0 core:

- Public-package update, repeated Composer install, standalone validation and
  setup pass. The earlier source override was archived and replaced by Composer's
  real tagged package at the locked commit.
- Full `composer qa` passes: strict manifest validation, dependency audit, coding
  standards, both PHPStan checks, 27 tests / 213 assertions and live
  REST/block/render/ORM checks.
- `vendor/bin/runtime doctor --json` reports 15 passing checks. Native
  `wp runtime doctor --json` and `wp runtime validate` match standalone output
  and exit codes from both the project root and a package subdirectory.
- DDEV Playwright passes. A separate authenticated Chromium smoke verifies
  homepage HTTP 200, `/wp-login.php` form and POST, `/wp-admin/` dashboard and
  no JavaScript errors; screenshots were visually inspected.
- Environment and Nginx file hashes remain unchanged. WordPress home/site URLs
  remain equal at the public root. Generated WordPress bootstrap loads the
  versioned environment-format helper successfully.
- `vendor/bin/runtime --check --json` reports no generated-file changes and
  exits 2 because custom WP-CLI effects remain unknown during inspection.

The long-running local fixture had drifted to WordPress 7.1.2; Composer restored
its existing locked 7.0 core before the final QA/browser pass. An earlier login
request timed out; the repeated authenticated check passed. No dependency
constraint, environment setting or web-server route was changed for that repair.
Upstream WP-CLI 2.12 PHP 8.5 deprecations remain; CLI parity checks consistently
suppress deprecation reporting. Production and independent migration field trials
were waived; these results cover automated and isolated consumer verification.

## Runtime 0.2.0 upgrade verification

The upgrade changes only `sympress/runtime` in the Composer dependency graph.
Follow the [0.2.0 upgrade guide](https://github.com/SymPress/runtime/blob/v0.2.0/docs/releases/0.2.0.md).
The standalone executable is now `vendor/bin/runtime`; Composer command names and
configuration keys remain unchanged. Existing root URLs, Nginx rules and the
project's WP-CLI orchestration are preserved.

A fresh installation downloaded WP-CLI 2.12.0. The committed
`sympress-runtime.lock` records the SHA-256 pins for its release PHAR, the upstream
SHA512 checksum response and the logical `wp-cli.phar` artifact. The PHAR digest
is `ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c`.
The guard file is ignored. Review source changes before explicitly accepting new
pins with `vendor/bin/runtime --update-lock`.

Verified on 2026-09-30 in an isolated DDEV project with PHP 8.5.9, Composer 2.10.3,
MariaDB 11.8 and a separate database:

- Composer installation and repeated standalone setup succeed.
- `vendor/bin/runtime validate` passes; local doctor reports 15 passing checks.
- Full `composer qa` passes: coding standards, both PHPStan checks, 27 tests /
  213 assertions, dependency audit and the live REST/block/render/ORM smoke.
- TypeScript checking, npm audit (zero findings), production asset build and the
  DDEV Playwright homepage test pass. Rebuilt assets are unchanged.
- Browser verification confirms HTTP 200 for the homepage, login form and POST at
  `/wp-login.php`, authenticated `/wp-admin/` dashboard and no JavaScript errors.
- `vendor/bin/runtime --check --json` reports no generated-file changes and exit 2
  because custom WP-CLI command effects are unknown during read-only inspection.
  This is not a clean deployment-drift certification.

WP-CLI 2.12.0 still emits upstream PHP 8.5 deprecation notices. Production rollout
and a two-week observation period were not part of this local verification.

## Original migration evidence

Original migration verification in a fresh DDEV project with a separate database:

- Composer installation succeeds and Runtime setup runs before asset-compiler.
- QA passed: 27 tests, 213 assertions, coding standards, PHPStan, Composer validation and dependency audit.
- The real REST, block registration, render and ORM runtime smoke passes.
- `wp console doctor --json` reports 14 passing checks, kernel boot count is one, and `wp console debug:container` succeeds.
- Browser smoke verifies a successful homepage response, the root `/wp-login.php` form action and authenticated root `/wp-admin/` dashboard without JavaScript errors.
- Neither the installed graph nor the lockfile includes a `wecodemore/*` package.
- Repeated Composer installation, global `composer install --no-plugins` and the standalone runner preserve 696 generated file hashes/link targets on the reviewed revision, including built assets and the deterministic package-layout record.

The former global `--no-plugins` failure is fixed by Runtime: offline preparation restores package paths and Composer autoload metadata before loading project code, preserving the regular installer workflow. Full replay, QA and root-URL browser smoke pass with this lockfile. Runtime's [mandatory integration and differential job](https://github.com/SymPress/runtime/actions/runs/36704369536) passes all 1,059 tests / 7,365 assertions without skips, the evidence check for 407 rows and 251 differential cases. Its [real WPackagist installation job](https://github.com/SymPress/runtime/actions/runs/36704369440) also passes. The original migration used a read-only deploy key before Runtime became public. Successful consumer CI is required before merge; Runtime's documented offline recovery boundaries still apply.

The review also refreshed compatible npm dependencies to resolve nine audit findings. `npm audit` reports zero vulnerabilities, and TypeScript analysis plus the production build pass locally and in GitHub CI.

Reproduce with `ddev composer install`, `ddev composer qa`, `ddev exec php wp-cli.phar console doctor --json`, and `ddev exec php wp-cli.phar console debug:container`. Runtime's `tools/consumer-smoke.mjs` uses private local credentials for browser verification. WP-CLI 2.12 can emit upstream PHP 8.5 deprecations; use `vendor/bin/runtime doctor --json` for clean JSON.

The original checkout's unrelated edits were preserved in place. This migration was developed in a separate Git worktree. Generated configuration, secrets and browser session data are excluded from the commit.

Runtime requires no SSH authentication. Optional CI forwarding of Composer SSH keys, Composer authentication and npm tokens remains available for other dependencies. When updating an existing environment from a previous `/wp` URL, run `vendor/bin/runtime flush-env-cache` so cached values cannot retain that URL.
