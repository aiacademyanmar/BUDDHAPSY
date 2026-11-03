#!/usr/bin/env python3
"""Static & structural checks: NOT Android compilation or live clinical verification."""
from pathlib import Path
import yaml, json, re
root=Path(__file__).resolve().parents[1]
wf=yaml.safe_load((root/'.github/workflows/build-apk.yml').read_text())
assert isinstance(wf,dict)
assert set(wf['jobs'])=={'source-security','mysql-ci-e2e','android-debug'}
assert wf['jobs']['android-debug']['needs']=='source-security'
steps=wf['jobs']['source-security']['steps']
guard=next(x for x in steps if x.get('name')=='HTTP unauthenticated and service-unavailable guard tests')
assert '\n' in guard['run'] and 'test_http_guards.py' in guard['run'] and 'test_https_gate.py' in guard['run']
assert wf['jobs']['mysql-ci-e2e']['services']['mysql']['image']=='mysql:8.0'
assert wf['jobs']['mysql-ci-e2e']['env']['APP_ENV']=='development' # loopback only; not real staging TLS
assert 'gradle --no-daemon' in next(x['run'] for x in wf['jobs']['android-debug']['steps'] if x.get('name')=='Build Android debug APK')
assert 'versionName = "1.7.2"' in (root/'app/build.gradle.kts').read_text()
assert (root/'settings.gradle.kts').is_file()
assert 'Response too large' in (root/'app/src/main/java/asia/aaii/buddhapsychologycare/CareApi.kt').read_text()
assert 'patient' in (root/'server/src/v171_routes.php').read_text()
assert 'CREATE TABLE IF NOT EXISTS consent_policy_versions' in (root/'server/database/006_v1_7_1_consent_policy.sql').read_text()
assert not (root/'app/build/outputs/apk/debug/app-debug.apk').exists()
print(json.dumps({'scope':'source/CI YAML only','passed':11,'total':11,'apk_built':False,'mysql_e2e_run':False},indent=2))
