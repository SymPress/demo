# Demo 1.1.5

The real Starter-compatible recipe now completes both the first deployment and a subsequent deployment. Kernel caches stay sealed at 0750, the permissions doctor runs as the PHP user through a narrowly scoped sudo rule, and SSH-agent forwarding is disabled. The recipe validates production WordPress policy through FPM before promotion and retains its database and post-promotion build-ID checks.

A dedicated CI job exercises two complete deployments on a disposable SSH/MariaDB/FPM host. A negative check verifies that a group-writable kernel cache is still rejected. The host test replaces the systemd boundary and public HTTPS probe; it executes the actual upload, WordPress, database, cache, symlink, doctor and FPM health operations.

Tracking-only query strings are removed before PHP, preventing campaign identifiers from entering the shared cached page. Semantic and mixed query strings retain their original bypass behavior. Native nginx/FPM tests cover the first miss and subsequent hit.

The release requires Runtime 1.2.4 and Monolog 1.1.4 and locks Framework Bundle 1.0.5, which fixes `lint:container` for already compiled Twig stacks. Scheduled update canaries use the fixed `v1.1.5` source baseline. Their future scheduled executions remain separate acceptance evidence.

`sympress/security` remains private and is neither required nor loaded. Its installation and vulnerability gate remain project decisions. CSP remains report-only. Existing hosts must grant the documented doctor sudo command to the deploy user and reload their nginx configuration after applying the tracking-query changes.
