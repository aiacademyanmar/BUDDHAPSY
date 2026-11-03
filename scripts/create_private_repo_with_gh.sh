#!/usr/bin/env bash
set -euo pipefail
# Run ONLY after reviewing source files; never upload PHI, .env, keys, or secrets.
REPO="aiacademyanmar/buddha-psychology-care"
command -v gh >/dev/null || { echo 'GitHub CLI (gh) is required'; exit 2; }
gh auth status >/dev/null
if [[ ! -d .git ]]; then
  git init -b main
  git add .
  git commit -m 'Buddha Psychology Care V1.7.2 CI source candidate'
fi
if gh repo view "$REPO" --json visibility --jq '.visibility' >/dev/null 2>&1; then
  [[ "$(gh repo view "$REPO" --json visibility --jq '.visibility')" == "PRIVATE" ]] || { echo "Refusing to push: $REPO is NOT private"; exit 3; }
  if ! git remote get-url origin >/dev/null 2>&1; then
    git remote add origin "git@github.com:${REPO}.git"
  fi
  git push -u origin main
else
  echo "Creating PRIVATE repository: $REPO"
  gh repo create "$REPO" --private --source . --remote origin --push
  [[ "$(gh repo view "$REPO" --json visibility --jq '.visibility')" == "PRIVATE" ]] || { echo 'Privacy verification FAILED'; exit 4; }
fi
printf 'Verified PRIVATE: https://github.com/%s\n' "$REPO"
