# Demo 1.1.8

- Restrict immutable caching to fingerprinted theme/plugin build assets. Uploads remain revalidatable even when their filenames contain a date or hash.
- Anchor project runtime rsync exclusions and preserve package-internal `var` files, while continuing to exclude credentials at every depth.
- Lock Asset Compiler 1.0.3 and ORM 0.3.4 after their publication.
- Advance the fixed update-canary source baseline to `v1.1.8`.

Private `sympress/security` remains absent from the dependencies and MU loaders.
