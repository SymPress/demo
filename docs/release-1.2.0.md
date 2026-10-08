# Demo 1.2.0

- Remove the local Base MU and complete `site-policy` packages. Shared website policies belong to separately activated private Natterer-Schaeffner packages; the public Demo has no private dependency.
- Verify published releases and rollback build IDs through the private PHP-FPM socket, retaining Core policy and database checks without a public health route.
- Refuse staging imports before decryption or mutation when no active WordPress mail guard is present.
- Keep the Demo feature plugin, required active Starter theme and retained application secret contract.
- Warm Twig templates before sealing the release cache so the first production page can render without PHP-worker writes.
- Advance the fixed update-canary source baseline to `v1.2.0`.

External health monitoring needs a project-owned route or the separately installed private Security MU loader. XML-RPC blocking in Security requires explicit `SYMPRESS_SECURITY_DISABLE_XMLRPC=true` configuration.
