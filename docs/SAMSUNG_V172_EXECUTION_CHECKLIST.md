# Samsung physical-device QA — V1.7.2 (NOT EXECUTED)

> Mark PASS only after recording actual device model, Android version, APK SHA-256 and video/logcat evidence.

| Test | Procedure | Status |
|---|---|---|
| APK signature | `apksigner verify --verbose --print-certs app-debug.apk` | NOT RUN |
| Installation | `adb install -r app-debug.apk` on a Samsung device | NOT RUN |
| Cold start | Launch 5 times; no splash freeze or crashes | NOT RUN |
| All 20 pages | Navigate 01–20; screenshot each | NOT RUN |
| Myanmar font | Unicode rendering, line wrap, touch sizes | NOT RUN |
| Offline behavior | Airplane mode; clearly show which modules are unavailable | NOT RUN |
| Login | Staging-only account; error handling and logout | NOT RUN |
| Consent | Inactive policy cannot grant; revocation works | NOT RUN |
| Background/rotation | Reopen, resume, screen rotation, process death | NOT RUN |
| Accessibility | TalkBack, font scaling, focus, contrast | NOT RUN |
| Security | No secrets or PHI in logs/backups; TLS behavior | NOT RUN |
| Telehealth | Must remain disabled without verified implementation | NOT RUN |

Device ID, screenshots, QA signer and pass/fail evidence must be populated during a real Samsung test. Do not use this document as proof of QA completion.
