#!/usr/bin/env python3
"""Verify APP_ENV=staging refuses plaintext HTTP at PHP entrypoint. No network external."""
import os, socket, subprocess, time, urllib.request, urllib.error, json
from pathlib import Path
root=Path(__file__).resolve().parents[1]
with socket.socket() as s:
    s.bind(('127.0.0.1',0));port=s.getsockname()[1]
env=dict(os.environ,APP_ENV='staging')
p=subprocess.Popen(['php','-S',f'127.0.0.1:{port}','-t',str(root/'server/public'),str(root/'server/public/index.php')],env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
try:
    for _ in range(50):
        try:
            urllib.request.urlopen(f'http://127.0.0.1:{port}/api/v171/health',timeout=2)
        except urllib.error.HTTPError as e:
            data=json.loads(e.read()); assert e.code==426 and data['error']=='HTTPS is required'
            print('PASS: APP_ENV=staging refuses insecure transport with HTTP 426')
            break
        except urllib.error.URLError:time.sleep(0.08)
    else:raise AssertionError('HTTPS transport guard did not respond as expected')
finally:
    p.terminate();p.wait(timeout=3)
