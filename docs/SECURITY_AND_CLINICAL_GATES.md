# V1.7 security, consent and clinical deployment gates

## Implemented in source
- Android internet API is HTTPS-only; no cleartext traffic, backup disabled, in-memory bearer token.
- MySQL/PDO prepared statements; password hashes via PASSWORD_DEFAULT; bearer tokens are random, SHA-256 hashed in database, expire after 7 days (legacy policy).
- Login attempt throttling in prior V1.4 source (10 tries/15 minutes per email or IP digest).
- Server-side role checks on v17 routes; per-user consent state and append-only events.
- Doctor directory only lists accounts with `verified=1`; old manual activation route disabled by default until external review and documented evidence exists.
- Learning catalog does not publish courses without editorial/clinical approval; media cannot publish without rights status.
- Admin release drafts never deploy code. Security incidents are drafting only and do not alert operations.

## Not implemented — must block clinical production
1. MFA and step-up authentication for doctors/admins; device-bound sessions; RBAC/ABAC per medical record; least-privilege production environment.
2. Doctor/psychologist credential verification against actual authority registry, evidence storage, license expiry and two-person approval.
3. Secure, encrypted clinical record storage with a real KMS/HSM, key rotation, deletion/retention policies, tested backups and access logging.
4. Telehealth end-to-end video provider, signed joining tokens, waiting-room security, clinician verification and connectivity testing.
5. AI medical guardrails, clinical evaluation, professional oversight, crisis escalation pathways, localized emergency contacts and verified model/provider privacy terms.
6. Clinical safety case, documented SOPs, regulatory/legal compliance, consent revocation propagation and threat modeling.
7. Real Android Gradle build, APK signing and install, Samsung/device testing, accessibility, penetration test, load test and independent security review.
8. Go-live authority and explicit sign-off; no automatic production release route is included.

## Operational note
The 20 Compose pages are a functional native **integration shell** based on earlier HTML design modules, not a pixel-perfect port or a fully operational end-to-end hospital/clinic platform.
