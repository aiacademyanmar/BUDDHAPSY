# GitHub connector status — 2026-11-03

- Authenticated user: `aiacademyanmar` (verified through connected GitHub integration).
- Private repository `aiacademyanmar/buddha-psychology-care`: not visible/accessible (GitHub repository read returned HTTP 404). A private repo not accessible to the linked integration can also return 404.
- The connected GitHub action set does not include repository creation. Nothing was pushed to the existing unrelated project repositories.
- `actions/checkout@v7`, `actions/setup-java@v6`, `actions/upload-artifact@v7`, `gradle/actions@v6`, and `actions/setup-python@v6` were checked as valid public GitHub refs through the GitHub integration.
- Local Git commit and bundle below are preparation artifacts only. Neither creates a GitHub-hosted repository nor runs CI.
- For private repo setup with GitHub CLI, review the source and run `bash scripts/create_private_repo_with_gh.sh` **in your authenticated environment**. The script refuses to push if an existing target repository is public. Confirm repository privacy and Actions permissions before running.
