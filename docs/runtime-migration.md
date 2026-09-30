# Runtime migration review

The demo replaces WP Starter with `sympress/runtime` and `dev-ops/runtime.json`. Compatibility is disabled; the previous dotenv file convention is explicit. The command provider reads shared Runtime preflight status and keeps the existing installation and seed commands. Its path is now project-root-relative. The base MU package resolves the project autoloader for both copied and linked installation and boots the kernel only once.

The dependency follows Runtime main in its private repository; the lockfile pins merged commit `60249ee25ead52f94e2b83e9be4ce472cd3b2fcf`. All six implementation PRs and [review corrections #7](https://github.com/SymPress/runtime/pull/7) are merged. Private repository access is required through SSH or Composer GitHub authentication. WP-CLI uses Runtime's SHA512-verified root phar, preserving CLI capabilities without the Composer bundle's Symfony Process dependency conflict. Static analysis reads the actual phar command class. Generated `public/index.php` is no longer tracked; setup creates it.

Public WordPress URLs remain at the site root: `WP_SITEURL=${WP_HOME}`. The original DDEV Nginx rules map public endpoints to the physical `public/wp` core directory. No Nginx routing changes are required by this migration.

Verification in a fresh DDEV project with a separate database:

- Composer installation succeeds and Runtime setup runs before asset-compiler.
- QA passed: 27 tests, 213 assertions, coding standards, PHPStan, Composer validation and dependency audit.
- The real REST, block registration, render and ORM runtime smoke passes.
- `wp console doctor --json` reports 14 passing checks, kernel boot count is one, and `wp console debug:container` succeeds.
- Browser smoke verifies a successful homepage response, the root `/wp-login.php` form action and authenticated root `/wp-admin/` dashboard without JavaScript errors.
- Neither the installed graph nor the lockfile includes a `wecodemore/*` package.
- Repeated Composer installation, global `composer install --no-plugins` and the standalone runner preserve 696 generated file hashes/link targets on the reviewed revision, including built assets and the deterministic package-layout record.

The former global `--no-plugins` failure is fixed by Runtime: offline preparation restores package paths and Composer autoload metadata before loading project code, preserving the regular installer workflow. Full replay, QA and root-URL browser smoke pass with this lockfile. Runtime's [mandatory integration and differential job](https://github.com/SymPress/runtime/actions/runs/36704369536) passes all 1,059 tests / 7,365 assertions without skips, the evidence check for 407 rows and 251 differential cases. Its [real WPackagist installation job](https://github.com/SymPress/runtime/actions/runs/36704369440) also passes. Demo CI currently fails while cloning the private Runtime repository because it lacks read access. No CI credentials or access settings have been changed. Keep this PR in draft until that access is configured and its CI passes; Runtime's documented offline recovery boundaries still apply.

The review also refreshed compatible npm dependencies to resolve nine audit findings. `npm audit` reports zero vulnerabilities, and TypeScript analysis plus the production build pass locally and in GitHub CI.

Reproduce with `ddev composer install`, `ddev composer qa`, `ddev exec php wp-cli.phar console doctor --json`, and `ddev exec php wp-cli.phar console debug:container`. Runtime's `tools/consumer-smoke.mjs` uses private local credentials for browser verification. WP-CLI 2.12 can emit upstream PHP 8.5 deprecations; use `vendor/bin/sympress-runtime doctor --json` for clean JSON.

The original checkout's unrelated edits were preserved in place. This migration was developed in a separate Git worktree. Generated configuration, secrets and browser session data are excluded from the commit.

GitHub rejects deploy keys for the private Runtime repository. All Composer and DDEV workflows now forward `COMPOSER_AUTH_JSON`; it must contain GitHub authentication with read access to Runtime. The rejected deploy-key attempt left no new keys or CI secrets. Changing the repository's deploy-key policy is not part of this migration. When updating an existing environment from a previous `/wp` URL, run `vendor/bin/sympress-runtime flush-env-cache` so cached values cannot retain that URL.
