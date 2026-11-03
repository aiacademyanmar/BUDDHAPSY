#!/usr/bin/env python3
"""Collect honest SOURCE verification results; does not compile Android or access MySQL."""
import json, subprocess, shutil, hashlib
from pathlib import Path
from datetime import datetime,timezone
root=Path(__file__).resolve().parents[1]
checks=[]
def run(name,command):
    p=subprocess.run(command,text=True,capture_output=True,cwd=root,timeout=30)
    checks.append({'name':name,'result':'PASS' if p.returncode==0 else 'FAIL', 'detail':(p.stdout+p.stderr).strip()[-1200:]})
run('Native 20-screen and HTML reference static integrity',['python3','qa/check_source.py'])
for p in sorted((root/'server').rglob('*.php')):
    run('PHP lint: '+str(p.relative_to(root)),['php','-l',str(p)])
run('Admin JS syntax',['node','--check','server/admin/app.js'])
# local dependency states
blocked=[]
if shutil.which('gradle') is None:blocked.append('Android Gradle build unavailable: Gradle CLI not installed in this workspace')
if not (Path('/opt/android-sdk').exists() or Path('/root/Android/Sdk').exists()):blocked.append('Android SDK 35 not installed; APK compilation and signature checks not executed')
mods=subprocess.run(['php','-m'],capture_output=True,text=True).stdout.lower()
if 'pdo_mysql' not in mods:blocked.append('PHP pdo_mysql extension unavailable; database integration not tested')
if 'mbstring' not in mods:blocked.append('PHP mbstring extension unavailable; PHP API runtime requires setup')
blocked+=['MySQL 8 database unavailable: SQL migration and CRUD integration not executed',
          'Real telehealth, licensed clinician verification, clinical safety review and Samsung QA not executed',
          'No GitHub Actions run, APK file, release signing or deployed server performed']
result={'project':'Buddha Psychology Care V1.7 Full Platform Integration Foundation',
        'date':'2026-11-03','mode':'source verification only',
        'source_checks':len(checks), 'source_checks_passed':sum(c['result']=='PASS' for c in checks),
        'source_checks_failed':sum(c['result']=='FAIL' for c in checks),
        'checks':checks,'external_dependencies_blocked':blocked,
        'production_readiness':'NOT READY', 'apk_build':'NOT ATTEMPTED (SDK/Gradle unavailable)',
        'clinical_status':'NO CLINICAL FEATURES APPROVED'}
(root/'qa/source_check_results.json').write_text(json.dumps(result,indent=2,ensure_ascii=False)+'\n')
print(f"Source checks: {result['source_checks_passed']}/{result['source_checks']} PASS; APK build NOT RUN; MySQL NOT RUN")
