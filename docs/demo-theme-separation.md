# Theme, demo plugin and project tooling

The website requires `sympress/theme-starter:^1.1.1` and the separate
`sympress/demo-plugin:^1.0`. It does not require the `sympress/starter` project.
The theme comes from its tagged GitHub repository; the demo plugin is developed
as a Composer path package under `packages/sympress-demo/`.

## Ownership

| Concern | Owner |
| --- | --- |
| Page shell, navigation, WordPress template hierarchy, Twig views, generic theme/editor assets | `sympress/theme-starter` and its `sympress/twig-bundle` dependency |
| Notes, taxonomy, REST, dynamic block, feature views/assets, seeding, migrations, admin, telemetry, profiler extension | `sympress/demo-plugin` |
| WordPress installation, site kernel, environment, site dependency lock, deployment and operations | Website project and base MU bootstrap |

The theme renders ordinary WordPress page content. That content includes the
plugin's `sympress-demo/notes` block. No demo class, template or stylesheet is
copied into the starter theme. Plugin filesystem paths come from its own
installation directory, and the bundle contributes its own ORM entity mapping.

## Installation and existing sites

The theme currently requires GitHub repository access. For the locked GitHub
ZIP download, supply `COMPOSER_AUTH_JSON` with a `github-oauth` credential that
can read the theme repository. An SSH-only installation additionally requires
an authorized `COMPOSER_SSH_KEY` and an explicit source-install preference for
this package; Composer does not automatically fall back from failed ZIP access.
The VCS repository is scoped to this one package.
Published library packages continue to resolve through Packagist.

The dependency canary requests `composer_update` in the reusable workflow for
scheduled and explicitly requested manual updates. Private repository metadata
is resolved during its isolated credential phase, with Composer plugins and
scripts disabled. Credentials are removed before normal project setup and
builds; the later normal install activates WordPress installers.

```sh
ddev composer install
ddev composer runtime:setup --no-interaction
ddev composer compile-assets --mode production
```

Fresh setup activates `sympress-starter`, activates the demo plugin and creates
the notes homepage. Existing installations receive only a database check;
their homepage and notes are preserved. Select the theme explicitly after an
upgrade:

```sh
ddev composer demo:theme
```

On an existing production or staging installation, this is an explicit theme
migration: prepare and back up the release, restrict traffic for the change,
then run `php wp-cli.phar theme activate sympress-starter` from the prepared
release before publishing it. The deployment health check requires that theme.
The old release must remain available; rolling back to a release without the
starter theme also requires restoring its previous theme selection. A symlink
rollback alone does not undo WordPress options in the database.

Both the theme and plugin declare their own asset-compiler metadata. The root
project allows their builds; the root deployment build is excluded from asset
compilation to prevent recursive builds.

## Reusing the demo plugin in another Composer website

This package can be installed with a path repository pointing at
`/path/to/demo/packages/sympress-demo`, independently of the starter theme. For
example, add this repository to the consuming website's `composer.json`:

```json
{
  "type": "path",
  "url": "../demo/packages/sympress-demo",
  "options": {
    "symlink": false,
    "versions": { "sympress/demo-plugin": "1.0.0" }
  }
}
```

Then require `sympress/demo-plugin:^1.0`, configure the usual WordPress plugin
installation path, allow its asset build and activate `sympress-demo` through
WordPress. The consuming website owns Composer autoloading and one `SiteKernel`
bootstrap. The feature plugin never creates another kernel.

The path repository's version is a local development declaration. No separate
Packagist release or new GitHub repository is implied. Application code is
portable; the demo's full QA suite still includes website integration contracts.

## Why `bin/console` exists

`bin/console` is the small entry point for this website's configured Symfony
Console application. It defines no duplicated command implementation.
`sympress/wp-cli-console` supplies `wp:*` commands to that application;
`sympress/cli` creates and updates projects without booting WordPress. These
three roles are distinct. The existing deployment recipe also uses the project
entry point for `lint:container`.

## Why `deployment/` exists

`deployment/composer.json` and its lockfile keep Deployer dependencies separate
from the website runtime. `deployment/deploy.php` only loads the recipe in the
website root. This is one recipe with two entry paths. The folder is an optional
hosting/deployment profile, not part of the feature plugin or theme. The demo
does not automatically deploy from its current workflows.

## Why `dev-ops` contains Python

`operations.py` implements optional administrative backup, restore, staging and
monitor commands. `render-nginx.py` validates configuration inputs and renders
NGINX files. Their Python tests protect these operations; other tests protect
asset build recursion and canary alarm/heartbeat behavior. Python is tooling
for those operations and CI, not required when PHP serves a normal request.

Several operational files currently match the starter project. That is template
duplication to manage with a reviewed shared source or explicit synchronization;
it is not demo application logic and does not justify moving it into the plugin.
Removing these files would also remove their documented operational capabilities.

## What `dev-ops/preload.php` does

It is an optional PHP-FPM OPcache preloading profile for a selected class list.
The corresponding `opcache.preload` settings in `php-production.ini` are
commented out. Ordinary OPcache remains usable without preloading. The demo
does not activate preloading, and theme/plugin separation does not require it.
Enable it only after measuring a benefit and testing process renewal on deploy.

## Verification contract

Package tests verify installation under a different directory, feature views,
asset paths and URLs, and plugin-owned ORM mapping without website entity lists.
Runtime QA verifies the plugin's REST route, block rendering and ORM presence.
Browser QA requires the active starter theme, six rendered note cards, plugin
frontend enhancement and the starter theme's compiled stylesheet, rendered
inline by default or served through a stylesheet link.

Deployment and operational scripts retain their existing tests. The theme's
source is consumed as a versioned dependency and is not modified in this demo.
