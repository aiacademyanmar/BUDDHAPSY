# Staging PHP/MySQL Migration Runbook — V1.7.1

**Not executed in this environment. This runbook is for an administrator on an isolated staging server.**

## Prerequisites
- PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`; MySQL 8.0+ with appropriate user/permissions; Apache/Nginx HTTPS termination configured to set `HTTPS=on` for PHP.
- Dedicated staging **database** `buddha_care_staging`; dedicated least-privilege runtime DB account; separate migration account; no production patient records.
- Independently tested staging backup and restore evidence before import. MySQL DDL is not transactionally rollback-safe; never assume that `ROLLBACK` undoes these migrations.
- MySQL client option file outside webroot, mode `chmod 600`, e.g. `/secure/mysql-staging.cnf` with `[client]` host/user/password settings. Never commit or upload this file.

## Fresh staging install only
```bash
chmod 600 /secure/mysql-staging.cnf
export MYSQL_DEFAULTS_FILE=/secure/mysql-staging.cnf
export STAGING_DB_NAME=buddha_care_staging
export STAGING_BACKUP_VERIFIED=yes
bash scripts/apply_staging_migrations.sh --confirm-staging
```
The script will refuse to run if staging already has tables, if its option file has loose permissions, or if the guard flags are absent. It rewrites hardcoded `USE buddha_care` references to staging only. For an **already populated** staging schema, do not rerun 001–004; review the schema then apply only the not-yet-applied migration(s), using a proper migration ledger and DBA approval.

## New V1.7.1 consent policy migration
`server/database/006_v1_7_1_consent_policy.sql` creates versioned policy metadata. New policies are `is_active=0`. Legal/clinical owners must review the complete documents, independently verify the HTTPS policy URI + SHA256, authorize an approver and activate a matching version before patients may grant new consent. No application button should set `is_active=1` by itself.

## API and authentication setup
Set server environment variables `DB_HOST`, `DB_NAME=buddha_care_staging`, `DB_USER`, `DB_PASSWORD`, `RATE_LIMIT_PEPPER` (32+ random characters), and `APP_ENV=staging` in PHP-FPM or the server process. Use TLS-only HTTPS, and a trusted reverse proxy that sets `$_SERVER['HTTPS']`. Token strings must never appear in logs, URLs or shared screenshots.

## Staging verification matrix
1. `GET /api/v171/health` → 200 only after DB and policy table exist; otherwise 503. Must NOT be mistaken for clinical readiness (`clinical_ready=false`).
2. POST login wrong credentials → 401; excessive failed attempts → 429; successful staging login requires seeded test account and real database.
3. Anonymous `GET/POST /api/v171/consents` → 401.
4. Authenticated patient `GET /api/v171/consent-policies` → available versioned policies, initially INACTIVE.
5. Grant on inactive policy → 403. After **externally reviewed/activated policy**, stale version → 409; matching version → status+append-only event transaction. Revoke even if policy version later changes; confirm status false + new event in same patient scope.
6. Role tests: no cross-user consent/history access, admin cannot use patient-only POST, expired token → 401, logout token revoked.
7. No clinician identity is verified, no telehealth encryption exists and no patient records should be imported before formal security/clinical approval.

## Rollback
Do NOT blindly reverse DDL on a live instance. Roll back application traffic, revoke affected tokens, restore a tested staging snapshot and verify data integrity under an approved runbook.
