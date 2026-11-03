#!/usr/bin/env python3
"""CI-only disposable MySQL & localhost PHP E2E. Do NOT run against production/staging databases."""
from __future__ import annotations
import os, subprocess, pathlib, time, urllib.request, urllib.error, json, uuid, sys
ROOT=pathlib.Path(__file__).resolve().parents[1]
assert os.environ.get('APP_ENV') == 'development', 'Refusing nondevelopment database environment'
assert os.environ.get('DB_HOST') in ('127.0.0.1','localhost'), 'Refusing remote database host'
assert os.environ.get('DB_NAME') == 'buddha_care', 'Requires disposable CI database'
assert os.environ.get('DB_PASSWORD') == 'ci_only_test_password_172', 'Refusing unknown database password to avoid live data'

def sql_client(source:pathlib.Path):
    env=os.environ.copy()
    env['MYSQL_PWD']=env['DB_PASSWORD']
    cmd=['mysql','--protocol=TCP','-h','127.0.0.1','-u',env['DB_USER'],'--default-character-set=utf8mb4']
    with source.open('rb') as f:
        p=subprocess.run(cmd,stdin=f,env=env,stdout=subprocess.PIPE,stderr=subprocess.PIPE,timeout=45)
    if p.returncode:raise RuntimeError(f'Migration {source.name} failed: '+p.stderr.decode('utf-8','replace')[:1500])
    print('MIGRATION PASS',source.name)

def request(path,method='GET',payload=None,token=None):
    data=json.dumps(payload).encode() if payload is not None else None
    headers={'Accept':'application/json'}
    if data is not None:headers['Content-Type']='application/json'
    if token:headers['Authorization']='Bearer '+token
    req=urllib.request.Request('http://127.0.0.1:18080/api'+path, data=data,headers=headers,method=method)
    try:
        with urllib.request.urlopen(req,timeout=8) as r:return r.status,json.load(r)
    except urllib.error.HTTPError as e:
        return e.code,json.load(e)

for f in sorted((ROOT/'server/database').glob('00[1-6]*.sql')):
    sql_client(f)
assert len(list((ROOT/'server/database').glob('00[1-6]*.sql')))==6
php_env=os.environ.copy()
server=subprocess.Popen(['php','-S','127.0.0.1:18080','-t','server/public','server/public/index.php'],cwd=ROOT,env=php_env,stdout=subprocess.DEVNULL,stderr=subprocess.PIPE)
checks=[]
def check(name,result):
    checks.append(name)
    if not result:raise AssertionError('E2E FAILED: '+name)
    print('PASS',name)
try:
    for attempt in range(50):
        try:
            status,r=request('/health')
            if status==200:break
        except Exception:pass
        time.sleep(.2)
    else:raise RuntimeError('Loopback PHP API did not start')
    check('health 200',status==200 and r.get('ok')==True)
    check('v171 health DB',request('/v171/health')[0]==200)
    check('anonymous consent policies 401',request('/v171/consent-policies')[0]==401)
    email='ci-'+uuid.uuid4().hex[:12]+'@example.invalid'
    password='Ci-only-password-172!'
    status,r=request('/auth/register','POST',{'name':'Test Learner (synthetic)','email':email,'password':password,'consent':True})
    check('registration',status==201 and 'id' in r.get('data',{}))
    check('duplicate registration conflict',request('/auth/register','POST',{'name':'Test Learner','email':email,'password':password,'consent':True})[0]==409)
    check('invalid login',request('/auth/login','POST',{'email':email,'password':'wrong'})[0]==401)
    status,r=request('/auth/login','POST',{'email':email,'password':password})
    check('authenticated login',status==200 and bool(r.get('token')))
    token=r['token']
    check('authenticated me',request('/v17/me',token=token)[0]==200)
    check('admin scope denied',request('/v17/admin/overview',token=token)[0]==403)
    status,r=request('/v171/consent-policies',token=token)
    check('policy retrieval',status==200 and len(r.get('data',[]))==4)
    check('inactive policies',all(int(x['is_active'])==0 for x in r['data']))
    check('inactive policy blocks grant',request('/v171/consents','POST',{'purpose':'telehealth','granted':True,'version':'1.7.1'},token)[0]==403)
    status,r=request('/v171/consents','POST',{'purpose':'telehealth','granted':False,'version':'invalid'},token)
    check('revocation allowed while policy inactive',status==200 and r.get('granted') is False)
    status,r=request('/v171/consents',token=token)
    check('consent state stored',status==200 and any(x['purpose']=='telehealth' and int(x['granted'])==0 for x in r['data']))
    status,r=request('/v171/consent-history',token=token)
    check('append-only consent event present',status==200 and any(x['purpose']=='telehealth' and int(x['granted'])==0 for x in r['data']))
    check('legacy consent write disabled',request('/v17/consents','POST',{'purpose':'telehealth','granted':True},token)[0]==410)
    check('logout',request('/auth/logout','POST',{},token)[0]==200)
    check('revoked token rejected',request('/v17/me',token=token)[0]==401)
    print(json.dumps({'test_scope':'disposable MySQL 8.0 + localhost PHP development-mode API; not external staging TLS','migration_count':6,'checks_passed':len(checks),'result':'PASS'}))
finally:
    server.terminate()
    try:server.wait(timeout=3)
    except subprocess.TimeoutExpired:server.kill()
