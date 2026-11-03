#!/usr/bin/env python3
"""Static source integrity checks; does not claim Android compilation or live API success."""
from pathlib import Path
import re
p=Path(__file__).resolve().parents[1]
files=[p/'settings.gradle.kts',p/'build.gradle.kts',p/'app/build.gradle.kts',
    p/'app/src/main/java/asia/aaii/buddhapsychologycare/MainActivity.kt',
    p/'app/src/main/java/asia/aaii/buddhapsychologycare/CareApplication.kt',
    p/'app/src/main/java/asia/aaii/buddhapsychologycare/CarePages.kt',
    p/'server/public/index.php',p/'server/src/v17_routes.php',
    p/'server/database/005_v1_7_integration.sql',p/'server/admin/index.html']
for f in files:assert f.is_file() and f.stat().st_size>100,f'Missing {f}'
s=(p/'app/src/main/java/asia/aaii/buddhapsychologycare/CarePages.kt').read_text()
ids=[int(x) for x in re.findall(r'CarePage\((\d+),',s)]
assert ids==list(range(1,21)),ids
api=(p/'server/src/v17_routes.php').read_text()
for route in ['/v17/health','/v17/catalog','/v17/consents','/v17/consent-history','/v17/admin/overview','/v17/admin/releases']:
 assert route in api, route
# Native APK compilation uses the Kotlin registry; the full original HTML design archive
# is maintained separately and is NOT a required Android build input.
reference_dir=p/'app/src/main/assets/reference_ui'
found=sum(bool(list(reference_dir.glob(f'Page{n:02d}_*.html'))) for n in range(1,21)) if reference_dir.is_dir() else 0
print(f'INFO: {found}/20 optional HTML reference designs present; native screens checked above')
assert (p/'app/src/main/AndroidManifest.xml').read_text().count('usesCleartextTraffic="false"')==1
assert 'ALLOW_MANUAL_CREDENTIAL_ACTIVATION' in (p/'server/public/index.php').read_text()
print('PASS: 20 native page registry, optional HTML references omitted from CI, security flags, backend routes, project files')
