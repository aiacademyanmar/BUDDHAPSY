# GitHub Repository Integration — V1.7.1

## Repository discovery result (2026-11-03)
Connected account: `aiacademyanmar`. GitHub connector returned 14 accessible repositories; none was a dedicated Buddha Psychology Care repository. No create-repository action was exposed, and no commits were pushed. Do **not** push this clinical prototype into an unrelated project repository.

## One-time repository setup
1. Create a new **private** repository in the connected GitHub account, e.g. `aiacademyanmar/buddha-psychology-care` (example name, not an existing URL). Keep it private while the healthcare platform is unapproved.
2. Extract this ZIP preserving top-level `.github/workflows/build-apk.yml` and `settings.gradle.kts` at repository root.
3. Run from repository root, replacing example URL with the repository actually created:

```bash
git init
git add .
git commit -m "Buddha Psychology Care V1.7.1 staging foundation"
git branch -M main
git remote add origin https://github.com/aiacademyanmar/<ACTUAL_REPOSITORY>.git
git push -u origin main
```

4. Set GitHub **Actions Variables** `CARE_API_BASE_URL` to the approved HTTPS staging API endpoint. No secret patient data, admin password, database credential, signing key or health dataset belongs in GitHub source or Actions variables.
5. In Actions, open **Buddha Psychology Care V1.7.1 CI (Staging / Debug only)**. A passing source-check is not proof of a passing Android job. Only download `BuddhaPsychologyCare-V1.7.1-STAGING-debug` when the Android job passes and the artifact has `APK_SHA256.txt`.
6. Review `app/build/outputs/apk/debug/app-debug.apk` package ID and signature. Do not publish a debug APK to patients.

## Build blockers
No repository created from this connector session; no GitHub Actions run ID; no APK hash. Any link claiming otherwise is unverified.
