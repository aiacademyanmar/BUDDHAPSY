# Buddha Psychology Care V1.7 Server (PHP 8.2+ / MySQL 8+)

This is an integration foundation, NOT an approved clinical system.

## Staging install
1. Create a MySQL 8 database and separate least-privilege migration/runtime users. Back up the database before all migrations.
2. Run SQL migrations in numerical order: `001_foundation.sql` through `005_v1_7_integration.sql` **once** on staging. The 002 migration changes `appointments` and is not safely repeatable without a migration runner.
3. Set `DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, RATE_LIMIT_PEPPER` in the **process environment**, not the source directory. The `.env.example` is documentation only.
4. Install PHP extensions: `pdo_mysql`, `mbstring`, `openssl`; configure PHP-FPM and HTTPS/TLS. Host the API behind a trusted reverse proxy mapping `/api` to `server/public/index.php`. Do not expose `server/src`, `server/database` or `.env`.
5. Deploy `admin/index.html`, `admin/app.js`, `admin/styles.css` same-origin behind HTTPS. Its CSP assumes same-origin `/api`. Admin sign-in calls `/api/auth/login` and never persists tokens to localStorage.
6. In CLI, create a staging admin with `php scripts/create_admin.php ...` following secure secret entry practice; keep credentials out of shell history and audit logs. Do NOT use this as a production enrolment/credentialing method.
7. Test HTTPS, login rate limits, migrations, token expiry, cross-user access, consent revocation, concurrency booking, Unicode Burmese, clinical review workflow, backup/restore, pentest and a regulatory/legal review before launch.

## Implemented V1.7 endpoints
`GET /api/v17/health` — DB health check (not a guarantee that the system is fully healthy)
`GET /api/v17/catalog` — ONLY published + clinically approved courses, licensed media, published future events
`GET /api/v17/me` — authenticated profile summary
`GET/POST /api/v17/consents` — patient-only purpose-scoped consent state and append-only events
`GET /api/v17/consent-history` — patient-only history
`GET /api/v17/admin/overview` — admin-only database summary
`GET /api/v17/admin/credential-queue` — admin-only pending credential review (NO automatic verification)
`GET /api/v17/admin/audit` — admin-only audit log
`GET/POST /api/v17/admin/releases` — admin-only DRAFT creation (NO deployment)
`GET/POST /api/v17/admin/incidents` — admin-only DRAFT creation (NO operational escalation)

Legacy routes remain: `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`, `/api/doctors`, `/api/appointments`, and earlier care/calendar routes.

## Critical limitations and blockers
- No deployed server, no database integration test, no live clinician identity validation, no production Telehealth, no generative clinical AI, no KMS/encrypted medical storage or MFA.
- Credential activation is disabled by default using `ALLOW_MANUAL_CREDENTIAL_ACTIVATION=false`. Do not enable without external licensing source verification, evidence capture, second review, and documented governance controls.
- Admin API bearer token is in browser memory only; production should implement MFA, short rotating sessions, device/session revocation, dedicated auth gateway and granular RBAC beyond the legacy three roles.
- Consent revocation creates a database event, but propagation to all downstream data processors/systems and legal consent document management are NOT implemented.
- Test and configure Apache Authorization forwarding; reject plaintext HTTP at edge.
- Sensitive content must not be put in release notes, incident drafts or test fixtures.

Admin draft catalog endpoints: `GET/POST /api/v17/admin/courses`, `/media`, `/events`. All POST routes are admin-only and create **drafts only**. This is deliberate; review and publishing workflows require additional gated implementation.
