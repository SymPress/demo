# Runtime migration review

The demo replaces WP Starter with `sympress/runtime` and `dev-ops/runtime.json`. Compatibility is disabled; the previous dotenv file convention is explicit. The command provider reads shared Runtime preflight status and keeps the existing installation and seed commands. Its path is now project-root-relative. The base MU package resolves the project autoloader for both copied and linked installation and boots the kernel only once.

The private Runtime phase-6 branch requires SSH repository access and must be retargeted after its PR stack is accepted. WP-CLI uses Runtime's SHA512-verified root phar, preserving CLI capabilities without the Composer bundle's Symfony Process dependency conflict. Static analysis reads the actual phar command class. Generated `public/index.php` is no longer tracked; setup creates it.

The DDEV Nginx configuration serves physical `/wp/wp-login.php` and `/wp/wp-admin/` endpoints directly. This prevents login POST loss and redirect loops with the explicit `WP_SITEURL=/wp` default.

Verification in a fresh DDEV project with a separate database:

- Composer installation succeeds and Runtime setup runs before asset-compiler.
- QA passed: 27 tests, 213 assertions, coding standards, PHPStan, Composer validation and dependency audit.
- The real REST, block registration, render and ORM runtime smoke passes.
- `wp console doctor --json` reports 14 passing checks, kernel boot count is one, and `wp console debug:container` succeeds.
- Browser smoke verifies a successful homepage response and authenticated admin dashboard without JavaScript errors.
- Neither the installed graph nor the lockfile includes a `wecodemore/*` package.
- Repeated Composer installation, global `composer install --no-plugins` and the standalone runner preserve 664 generated file hashes/link targets, including built assets and the deterministic package-layout record.

The former global `--no-plugins` failure is fixed by Runtime `cfc5ffd`: offline preparation restores package paths and Composer autoload metadata before loading project code, preserving the regular installer workflow. The full replay and demo QA pass after recovery. Real WPackagist plugin/theme/core installation also passes in Runtime's separate integration job. Final acceptance remains open for Runtime's documented layout boundaries and remote QA, which awaits the separate QA PHPUnit 13 change. Keep this PR in draft until those gates and the Runtime stack are resolved.

Reproduce with `ddev composer install`, `ddev composer qa`, `ddev exec php wp-cli.phar console doctor --json`, and `ddev exec php wp-cli.phar console debug:container`. Runtime's `tools/consumer-smoke.mjs` uses private local credentials for browser verification. WP-CLI 2.12 can emit upstream PHP 8.5 deprecations; use `vendor/bin/sympress-runtime doctor --json` for clean JSON.

The original checkout's unrelated edits were preserved in place. This migration was developed in a separate Git worktree. Generated configuration, secrets and browser session data are excluded from the commit.
