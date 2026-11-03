# Private GitHub repository setup — V1.7.2

**Status:** Repository creation, push and CI workflow execution NOT completed. The connected GitHub app has no create-repository action; do not push this confidential healthcare project to an unrelated public repository.

1. On the authenticated `aiacademyanmar` GitHub account, create a **private** repository named `buddha-psychology-care` (do not initialize README, .gitignore or license). Verify its visibility is PRIVATE before any push.
2. From the extracted V1.7.2 source directory, run:

```sh
git init -b main
git add .
git commit -m "Buddha Psychology Care V1.7.2 CI and MySQL integration tests"
git remote add origin git@github.com:aiacademyanmar/buddha-psychology-care.git
git push -u origin main
```

3. Repository Settings → Actions: allow appropriate GitHub Actions runner execution. Set Actions variable `CARE_API_BASE_URL` to your **staging HTTPS** endpoint, never a private production patient system. API Base URL must include `/api`.
4. Actions → `Buddha Psychology Care V1.7.2 CI (Debug + MySQL CI E2E)` → Run workflow. Check **source-security**, **mysql-ci-e2e**, **android-debug** independently. The MySQL E2E job uses disposable CI test credentials and synthetic data, NOT a deployed staging database.
5. If android-debug succeeds, download `BuddhaPsychologyCare-V1.7.2-DEBUG-not-clinical` artifact containing `app-debug.apk` and `APK_SHA256.txt`. Compare hash, scan, then follow Samsung QA checklist. A debug APK is NOT a clinical production release.

**No GitHub Actions status, commit URL, real APK, staging data migration, or device test is asserted by this document.**
