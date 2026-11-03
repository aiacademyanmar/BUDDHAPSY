# Samsung Native Android Device QA Checklist — V1.7.1

**Status: NOT TESTED. No Samsung device, Android SDK or APK was available in this environment.**

| ID | Scenario | Expected acceptance | Result |
|---|---|---|---|
| S01 | Install debug APK and cold launch | Package `asia.aaii.buddhapsychologycare`, no splash freeze/crash | NOT RUN |
| S02 | Page 01–20 navigation | All 20 native shells open, footer/menu work | NOT RUN |
| S03 | Offline boot without internet | No crash, clearly marked offline/prototype | NOT RUN |
| S04 | Language rendering | Myanmar Unicode strings readable and not clipped | NOT RUN |
| S05 | Back/background/rotate | No crash; sensitive memory session not in saved state | NOT RUN |
| S06 | HTTPS endpoint configured | No plain HTTP or TLS downgrade; bad cert rejected | NOT RUN |
| S07 | Login wrong/expired credentials | Error displayed, no token disclosure | NOT RUN |
| S08 | Login rate limit | HTTP 429 handled, repeated login safe | NOT RUN |
| S09 | Consent policy inactive | No grant; clear explanation to user | NOT RUN |
| S10 | Policy version mismatch | 409 prompts policy refresh | NOT RUN |
| S11 | Consent revocation | Revoke works with matching patient, records event | NOT RUN |
| S12 | Doctor directory | Only independently verified doctors visible | NOT RUN |
| S13 | Booking conflict | Two users cannot book same slot | NOT RUN |
| S14 | Clinical placeholders | Telehealth/clinical AI not falsely represented as live | NOT RUN |
| S15 | Android network interruption | Clear error, retry works, no duplicate consent event from silent retry | NOT RUN |
| S16 | App permissions | No unexpected microphone/camera access in staging shell | NOT RUN |
| S17 | Samsung font scaling | 100%, 150%, 200% text sizes, accessibility labels | NOT RUN |
| S18 | Samsung One UI layouts | Phone portrait, landscape, device dark mode, edge-to-edge | NOT RUN |
| S19 | App logs/screenshots | No token or health record leaks to logcat/screenshot | NOT RUN |
| S20 | Logout/force-stop | In-memory token cleared, no auto-auth after restart | NOT RUN |
| S21 | API under real staging MySQL | 200/401/403/409/429/503 handling documented and tested | NOT RUN |
| S22 | Authorized release signing | Not applicable; debug APK not distributable | NOT RUN |

### Device evidence to capture
Samsung model, One UI version, Android version, APK SHA-256, build run URL, test date, tester name, pass/fail with screenshots/logcat redacted, signoff. Never include patient data in QA evidence.
