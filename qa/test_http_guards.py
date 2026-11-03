#!/usr/bin/env python3
"""Test only unauthenticated guard and unavailable-db response using PHP local server. No MySQL needed."""
from pathlib import Path
import subprocess, time, urllib.request, urllib.error, socket, os, json
root=Path(__file__).resolve().parents[1]
with socket.socket() as s:
    s.bind(('127.0.0.1',0));port=s.getsockname()[1]
env=dict(os.environ,APP_ENV='testing',DB_HOST='127.0.0.1',DB_NAME='unconfigured_smoke',DB_USER='no_user',DB_PASSWORD='')
proc=subprocess.Popen(['php','-S',f'127.0.0.1:{port}','-t',str(root/'server/public'),str(root/'server/public/index.php')],env=env,stdout=subprocess.DEVNULL,stderr=subprocess.PIPE,text=True)
checks={}
def request(path,method='GET',data=None):
    url=f'http://127.0.0.1:{port}/api{path}'
    req=urllib.request.Request(url,method=method,headers={'Content-Type':'application/json'},data=data)
    try:
        with urllib.request.urlopen(req,timeout=4) as r:return r.status,json.loads(r.read())
    except urllib.error.HTTPError as e:return e.code,json.loads(e.read())
try:
    for i in range(40):
        if proc.poll() is not None:raise RuntimeError('PHP server exited unexpectedly')
        try:request('/v171/me');break
        except (urllib.error.URLError,ConnectionError):time.sleep(0.1)
    for path in ['/v171/consent-policies','/v171/consents','/v171/consent-history','/v17/me','/v17/consents']:
        code,j=request(path);checks['unauthorized_'+path]=code==401 and 'error' in j
    code,j=request('/v17/consents','POST',b'{"purpose":"telehealth","granted":true,"version":"fake"}');checks['legacy_consent_grant_blocked']=code==410
    code,j=request('/v171/consents','POST',b'{"purpose":"telehealth","granted":true,"version":"fake"}');checks['new_consent_requires_auth']=code==401
    code,j=request('/v171/health');checks['unconfigured_database_is_not_healthy']=code==503 and not j.get('ok',True)
    code,j=request('/auth/login','POST',b'{"email":"bad","password":"x"}');checks['invalid_login_prevalidated']=code==401
    code,j=request('/v171/invalid');checks['unknown_path_404']=code==404
finally:
    proc.terminate()
    try:proc.communicate(timeout=3)
    except subprocess.TimeoutExpired:proc.kill()
result={'type':'unauthenticated_http_guards','count':len(checks),'passed':sum(checks.values()),'checks':checks,'notice':'No successful login, DB migration, authorized consent, live TLS or device tests.'}
(root/'qa/v171_http_guard_results.json').write_text(json.dumps(result,indent=2))
print(json.dumps(result,indent=2))
assert all(checks.values()),'HTTP guard tests failed'
