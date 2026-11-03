# Staging, Android build and release guide

## Android
1. Open repository root in Android Studio (JDK 17, Android SDK Platform 35, Gradle 8.9).
2. Configure trusted HTTPS backend: `gradle :app:assembleDebug -PcareApiBaseUrl=https://your-staging-domain.example/api`.
3. On GitHub, set Actions variable `CARE_API_BASE_URL` to staging API HTTPS endpoint. If absent, the CI build uses `https://api.example.invalid/api` so app is OFFLINE/demo only.
4. Run workflow `Buddha Psychology Care V1.7 Android CI` on main/feature branch.
5. CI verifies `app-debug.apk` package ID and debug signature; download CI artifact to a nonclinical Samsung test phone. Inspect device logs and perform manual tests.
6. Debug APK MUST NOT be published as clinical production APK. Production needs secure release signing and authorized clinical release approval.

## Server
1. Apply migrations 001–005 in staging with backups and a migration log.
2. Configure process environment, php extensions, HTTPS reverse proxy and access restrictions. Import no real patient data to unreviewed staging.
3. Run `find server -name '*.php' -print0 | xargs -0 -n1 php -l`; `node --check server/admin/app.js`.
4. Check `/api/v17/health`, then login, doctor booking, consent APIs, RBAC with different test accounts, logout.
5. Complete security/clinical gates described in `SECURITY_AND_CLINICAL_GATES.md`.

## Release artifacts
The source ZIP is not an APK. No SHA-256 signed APK or GitHub Actions run exists in this session.
