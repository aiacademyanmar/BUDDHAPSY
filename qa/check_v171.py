#!/usr/bin/env python3
"""Offline source invariants; NOT a Gradle build or database integration proof."""
from pathlib import Path
from xml.etree import ElementTree as ET
import re, json
root=Path(__file__).resolve().parents[1]
checks={}
def check(k,val):
    checks[k]=bool(val)
    if not val: raise AssertionError(k)
a=(root/'app/build.gradle.kts').read_text()
b=(root/'build.gradle.kts').read_text()
ui=(root/'app/src/main/java/asia/aaii/buddhapsychologycare/CareApplication.kt').read_text()
c=(root/'app/src/main/java/asia/aaii/buddhapsychologycare/CareApi.kt').read_text()
r=(root/'server/src/v171_routes.php').read_text()
m=(root/'server/database/006_v1_7_1_consent_policy.sql').read_text()
ci=(root/'.github/workflows/build-apk.yml').read_text()
check('version_bumped', ('versionName = "1.7.1"' in a and 'versionCode = 171' in a) or ('versionName = "1.7.2"' in a and 'versionCode = 172' in a))
check('kotlin_gradle_compatibility_update', b.count('2.2.21')==2 and '8.7.3' in b)
check('android_https_only', 'startsWith("https://")' in a and 'instanceFollowRedirects = false' in c)
check('no_persistent_auth_token', 'localStorage' not in c and 'SharedPreferences' not in c)
check('new_consent_endpoint_android', '"/v171/consents"' in ui and '"/v171/consent-policies"' in ui)
check('consent_version_server_lock', 'FOR UPDATE' in r and 'hash_equals' in r)
check('consent_revocation_without_active_policy', 'if($granted)' in r and '$previous=' in r)
check('consent_policies_inactive_by_default', "is_active TINYINT(1) NOT NULL DEFAULT 0" in m)
check('consent_legacy_write_disabled', "Legacy consent writes disabled" in (root/'server/src/v17_routes.php').read_text())
check('no_automatic_clinical_approval', 'ALLOW_MANUAL_CREDENTIAL_ACTIVATION' in (root/'server/public/index.php').read_text())
check('ci_debug_not_release', ':app:assembleDebug' in ci and 'apksigner' in ci and 'upload-artifact@v7' in ci)
check('api_headers_and_tls', "HTTPS is required" in (root/'server/public/index.php').read_text())
check('staging_migration_only', 'STAGING_DB_NAME' in (root/'scripts/apply_staging_migrations.sh').read_text())
check('valid_android_manifest', ET.parse(root/'app/src/main/AndroidManifest.xml').getroot().tag=='manifest')
check('twenty_mapped_screens', len(list((root/'app/src/main/assets/reference_ui').glob('Page*_Original_UIUX_Reference.html')))==20)
result={'type':'static_source_checks','count':len(checks),'passed':sum(checks.values()),'checks':checks,'notice':'Does not prove Android compilation, MySQL migration, staging integration, device QA or security certification.'}
(root/'qa/v171_static_results.json').write_text(json.dumps(result,indent=2))
print(json.dumps(result,indent=2))
