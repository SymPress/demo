# Repository security controls

The private Security package is not installed, required or loaded by Demo. Its
enumeration guard, MIME sanitizer and nonce middleware remain outside this site.
Existing author URLs are not changed automatically. Runtime supplies the native
production configuration; the production nginx example denies `xmlrpc.php`.
Website health, staging mail suppression and optional WordPress XML-RPC hooks
belong to the separately activated private Security MU loader. This public Demo
ships no local site-policy package. Staging sync requires an active mail guard.

`dev-ops/production.env.example` supplies hardening, filtered HTML/uploads and a
narrow WordPress outbound HTTP allowlist. Replace the complete host list for
reviewed payment, mail or other API integrations. Runtime production doctor and
the existing deployment permission/build-ID checks remain mandatory.

The nginx fragment adds broader report-only CSP, Permissions-Policy and COOP;
the enforced frame-ancestors policy stays in place. HSTS is enabled only after
TLS acceptance. Nonce-based CSP enforcement is not supplied by this template;
review third-party editor/payment/embed behavior before enforcing a policy.

The read-only, non-root PHP Compose fragment mounts only uploads, cache, logs,
temporary state and private secrets for writes. Provision the immutable image,
volume ownership, upstream server/database and site-specific TLS Redis ACL before
using it. The internal PHP network has no unrestricted Internet route; required
egress must be provisioned separately by the operator. The FPM-only INI disables
process execution and sets a site-specific open_basedir; CLI/migration workers
use their own PHP configuration and credentials. Adapt the root path and measure
the realpath-cache cost on the actual host.

The SQL grant example separates a DML-only web user from a migration identity
with schema privileges limited to one database. Neither gets FILE or global
grants. Do not inject migration credentials into FPM or persist them in dumps.

Daily Composer audits and dependency canaries stay enabled without heartbeat or
alert services. The Demo plugin's npm lock is updated within its existing version
ranges and must pass typecheck, production build and a moderate-or-higher audit.
New critical plugins need source/maintenance review, explicit privileges and
API-host documentation before admission.

The reusable deployment workflow provides a CycloneDX inventory, mandatory
archive digest verification and optional provenance/SBOM attestations. Production
callers must pin the reviewed signer workflow commit and grant its isolated
attestation job the required permissions. This verifies artifact transfer, not
live filesystem integrity; private Security manifest checks are not invoked here.

See [threat model](threat-model.md) and [incident procedure](incident-response.md).
