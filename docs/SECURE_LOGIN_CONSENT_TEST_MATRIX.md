# Secure Login & Consent API Verification Matrix

| Test | Expected response | Local evidence |
|---|---|---|
| No token for `/v171/consents` GET/POST | 401 | PASS (HTTP guard only) |
| No token for `/v171/consent-policies` | 401 | PASS (HTTP guard only) |
| Previous `/v17/consents` POST | 410 (cannot bypass policy version) | PASS (HTTP guard only) |
| Database not configured on `/v171/health` | 503, `ok=false` | PASS (local unavailable-mode only) |
| Invalid-format login email | 401 | PASS (pre-validation only) |
| Valid-format wrong password | 401 | NOT RUN (DB unavailable) |
| Rate-limited login attempts | 429 | NOT RUN (DB unavailable) |
| Correct patient password | 200 and new opaque token | NOT RUN (DB unavailable) |
| Expired/revoked token | 401 | NOT RUN (DB unavailable) |
| Cross-account ledger read | Denied | NOT RUN (DB unavailable) |
| Grant with inactive policy | 403 | NOT RUN (DB unavailable; code path reviewed) |
| Grant stale version | 409 | NOT RUN (DB unavailable; code path reviewed) |
| Grant matching active/approved policy | 200 + status + append-only event | NOT RUN (requires reviewed policy) |
| Revoke after policy rollover | 200 + status false + event | NOT RUN (DB unavailable) |
| HTTPS enforcement | 426 for staging/production if PHP sees plaintext transport | SOURCE CHECK ONLY |
| End-to-end mobile login and consent | UI + API + DB | NOT RUN (no APK/DB) |

## Production blockers
The current `patient/doctor/admin` legacy roles are coarse; implement least-privilege assigned-clinician authorization, MFA, short-lived rotating sessions, account recovery, legal policy document rendering and audit monitoring before real healthcare usage. The app does not support diagnosis, telehealth, personal clinical records or emergencies.
