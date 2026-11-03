#!/usr/bin/env bash
# Explicit staging-only guarded migration. Run ONLY on a restored staging database.
set -euo pipefail
if [[ "${1:-}" != "--confirm-staging" ]]; then
  echo 'Usage: MYSQL_DEFAULTS_FILE=/secure/mysql-staging.cnf scripts/apply_staging_migrations.sh --confirm-staging' >&2
  echo 'Refusing to execute migrations without explicit --confirm-staging.' >&2
  exit 2
fi
: "${MYSQL_DEFAULTS_FILE:?Set MYSQL_DEFAULTS_FILE to a chmod 600 MySQL option file}"
[[ -f "$MYSQL_DEFAULTS_FILE" && ! -L "$MYSQL_DEFAULTS_FILE" ]] || { echo 'Invalid defaults file' >&2; exit 3; }
if [[ $(stat -c %a "$MYSQL_DEFAULTS_FILE") != 600 ]]; then echo 'MySQL option file must be chmod 600' >&2; exit 3; fi
command -v mysql >/dev/null || { echo 'mysql CLI not installed' >&2; exit 4; }
[[ "${STAGING_DB_NAME:-}" == "buddha_care_staging" ]] || { echo 'STAGING_DB_NAME must be buddha_care_staging' >&2; exit 5; }
# The original migrations include USE buddha_care; rewriting only this literal for staging.
# A separate staging-only account and BACKUP must already be established by the operator.
[[ "${STAGING_BACKUP_VERIFIED:-}" == "yes" ]] || { echo 'Backup/restore evidence required: STAGING_BACKUP_VERIFIED=yes' >&2; exit 6; }
root=$(cd "$(dirname "$0")/.." && pwd)
existing=$(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" --batch --skip-column-names -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='buddha_care_staging'" )
[[ "$existing" == "0" ]] || { echo 'Staging schema is not empty. Refusing to replay non-idempotent migrations.' >&2; exit 7; }
for migration in "$root"/server/database/00*.sql; do
  echo "Applying $(basename "$migration") to staging (NOT idempotent for 002..004)"
  sed -e 's/USE buddha_care;/USE buddha_care_staging;/g' -e 's/CREATE DATABASE IF NOT EXISTS buddha_care /CREATE DATABASE IF NOT EXISTS buddha_care_staging /g' "$migration" |
    mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" --batch --show-warnings
 done
 echo 'Staging migrations completed; verify schema and run integration tests before claiming success.'
