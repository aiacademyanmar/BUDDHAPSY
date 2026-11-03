# Buddha Psychology Care — V1.7.1 Verified Development Report
Date: 2026-11-03 (Asia/Yangon)

## Scope and provenance
This is a patch to the verified, locally available V1.7 source ZIP. The V1.7 package contained 20 Kotlin screen-registry entries and 20 archived UI-reference HTML files, not 20 fully implemented production clinical screens. V1.7.1 preserves this distinction.

## Completed and locally verified
- Copied the V1.7 Android, PHP/MySQL, admin, API, 20-page reference HTML and source QA into the V1.7.1 project folder.
- Changed app to `versionName 1.7.1` and `versionCode 171`; revised Kotlin Gradle plugin and Compose compiler plugin from 2.0.21 to 2.2.21 to align with AGP 8.7.3 + Gradle 8.9 (compatibility source: Kotlin official docs). This is a **compatibility change, not proof of Gradle compilation**.
- Hardened Android HTTPS JSON client against redirect-following/downgrade and unbounded response processing. Tokens remain process-memory-only, never written to disk by this application.
- Added draft/inactive policy version table (`006_v1_7_1_consent_policy.sql`), new version-enforced consent routes `/api/v171/consent-policies`, `/api/v171/consents`, `/api/v171/consent-history`; switched Android consent UI to this path.
- Disabled legacy unversioned `/api/v17/consents` **POST** with HTTP 410 (intentional breaking change). Existing GET remains for older clients.
- Added server transport enforcement for `APP_ENV=staging|production`, and credentials throttle-secret configuration checks. Staging HTTPS termination must set `$_SERVER['HTTPS']` at trusted web server; do not blindly trust forwarded headers.
- Created an explicit staging-only migration script that requires a 0600 MySQL options file, staging DB name and verified pre-migration backup; refuses nonempty schemas. It is NOT a schema upgrade runner for a populated staging DB.
- Updated GitHub Actions YAML to contemporary Node24-compatible checkout/setup-java/setup-gradle/upload-artifact versions, prebuild PHP/HTTP guard tests, Android 35 SDK and Gradle 8.9 debug build with `aapt`/`apksigner` verification. **Workflow not run on GitHub**.
- Local static checks: **15/15 passed**; unauthenticated HTTP guard tests: **10/10 passed**; staging HTTPS rejection test: **passed**; legacy source mapping checks: **passed**; PHP source files: **all syntax-linted successfully**; Admin JS syntax: **passed**; staging script `bash -n`: **passed**; `CarePages.kt` standalone Kotlin compilation: **passed**.

## Not completed / not verified — do not use as success claims
- **GitHub repository integration:** not performed. The linked GitHub `aiacademyanmar` account has no dedicated Buddha Psychology Care repository among the 14 listed accessible repositories. Connector has no create-repository action. Project was **not uploaded to unrelated repositories**. Repository URL, commit SHA, Actions run URL: **none**.
- **Android APK:** not built, not signed, not installed. Local environment lacks Android SDK, Gradle distribution, and dependency downloads. Kotlin screen-registry compile is not Compose compile.
- **Staging database migration:** SQL and staging script were created, but not executed against a MySQL 8 staging server. No MySQL server, client, `pdo_mysql` or `mbstring` extension in local environment. DB schema state unknown.
- **Successful login:** no live MySQL-backed login. Tests proved only invalid-email validation, unauthorized requests rejected, and unavailable DB reported 503.
- **Consent authorization with real accounts:** not executed. Inactive policy defaults intentionally reject new grants until reviewed legal document published. Revoke behavior coded and source-checked; no real database event validated.
- **Samsung QA:** not performed; no Samsung test device accessible. Device checklist is prepared, not signed off.
- **Clinical safety and production:** no credential verification, formal clinical approval, full app integration, secure telehealth or production launch.

## Precise test evidence
See `qa/v171_static_results.json`, `qa/v171_http_guard_results.json` and `qa/source_check_results.json`; tests explicitly identify what they do not verify.

## Stop/go gate
**GO-LIVE BLOCKED.** Do not enter real patient data into this staging prototype. An authorized Android APK compile, staging migration + restore test, access-control and consent tests with a real DB, independent security review, verified clinicians and manual Samsung QA are prerequisites for any real-user release.

## Final local test summary
19/19 scoped checks PASS, including 11 PHP syntax checks. This is **not** an Android compile, database migration, device test or security certification. Full machine-readable results in `qa/v171_final_qa_results.json`.
