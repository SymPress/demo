# 1.1.0 repository security changes

Patch 1.1.1 retains browser-default payment permissions in the nginx template so
Payment Request checkout integrations are not disabled by the security headers.

Use the public Composer-managed Runtime 1.2 production defaults and the released
Monolog security audit/Profiler permission fixes. Private Security is not required
or loaded. The updated Demo plugin npm lock removes current advisories within its
existing dependency version ranges; typecheck and production build pass.

Production templates add report-only CSP, Permissions-Policy/COOP, FPM process and
file boundaries, a non-root read-only container fragment and separate web/migration
database grant examples. Hosting and egress provisioning remain operator-owned.
HSTS remains opt-in after TLS acceptance. Threat-model and incident instructions
record these boundaries without claiming an external scan or live-server changes.
