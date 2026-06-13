# Contributing

Thanks for helping improve SymPress Demo.

This repository is a reference WordPress website. Changes should make it easier for developers to understand how the public SymPress packages fit together in a real project.

## Project Shape

- Root project: Composer-based WordPress website.
- Demo package: `packages/sympress-demo`.
- Local runtime: DDEV.
- WordPress docroot: `public`.
- WordPress core: `public/wp`.
- WordPress content: `public/wp-content`.

## Guidelines

- Keep WordPress as the runtime.
- Keep hooks thin and move behavior into services.
- Keep domain code free of WordPress globals and functions.
- Prefer explicit configuration over hidden bootstrapping.
- Keep examples small enough to read.
- Add focused tests when behavior changes.
- Update the README when a change teaches a new SymPress concept.
- Do not commit local runtime caches such as `.phpunit.cache`, `.phpunit-cache`, `.pgpunit-cache` or package `var/cache` output.

## Quality Checks

```bash
ddev composer install
ddev composer qa
```

## Pull Requests

Please include:

- What changed
- Why it helps developers learn SymPress
- Any follow-up work that should stay out of the current PR
