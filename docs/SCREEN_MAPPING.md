# Screen mapping — Native Compose vs prior HTML UI designs

Original interactive HTML is retained as a **source reference**, not embedded as a WebView. V1.7 provides a consistent native layout and UI for each page; deep functionality is staged and must not be confused with production integration.

| Page | Module | Native V1.7 | API status | Reference file |
|---|---|---|---|---|
| 01 | Home | Native shell; login & navigation | Native | `Page01_Original_UIUX_Reference.html` |
| 02 | Learning Academy | Published catalog; course shells | Catalog GET | `Page02_Original_UIUX_Reference.html` |
| 03 | Doctor Booking | Verified doctors; booking navigation | Doctor GET | `Page03_Original_UIUX_Reference.html` |
| 04 | Media & Community | Published media catalog | Catalog GET | `Page04_Original_UIUX_Reference.html` |
| 05 | Appointment Calendar | Booking request and list | Booking API | `Page05_Original_UIUX_Reference.html` |
| 06 | Media Manager | Media catalog and draft workflow | Server admin draft | `Page06_Original_UIUX_Reference.html` |
| 07 | Community Management | Community feature outline | Preview | `Page07_Original_UIUX_Reference.html` |
| 08 | Events & Live Learning | Published events catalog | Catalog GET | `Page08_Original_UIUX_Reference.html` |
| 09 | Live Learning Room | Learning material catalog; presentation outline | Preview | `Page09_Original_UIUX_Reference.html` |
| 10 | Assessment & Certificate | Assessment outline | Preview | `Page10_Original_UIUX_Reference.html` |
| 11 | AI Wellness & Study Plan | Scripted tips; local note | Local only | `Page11_Original_UIUX_Reference.html` |
| 12 | Referral & Care Team | Verified directory selection | Partial API | `Page12_Original_UIUX_Reference.html` |
| 13 | Patient Records & Consent | Purpose consent read/revoke/grant | Consent API | `Page13_Original_UIUX_Reference.html` |
| 14 | Care Progress & Follow-up | Local education checklist | Local only | `Page14_Original_UIUX_Reference.html` |
| 15 | Telehealth Consultation | Video/waiting room outline | Not connected | `Page15_Original_UIUX_Reference.html` |
| 16 | Consultation & Aftercare | Local education checklist | Local only | `Page16_Original_UIUX_Reference.html` |
| 17 | Clinical Quality & Outcomes | Review/quality outline | Not connected | `Page17_Original_UIUX_Reference.html` |
| 18 | Governance & Safety | Governance/credential outline | Not connected | `Page18_Original_UIUX_Reference.html` |
| 19 | Security & Privacy | Consent and privacy overview | Partial API | `Page19_Original_UIUX_Reference.html` |
| 20 | Integration & Launch | Actual health ping and deployment gates | Health API / UI | `Page20_Original_UIUX_Reference.html` |

**Source audit:** all 20 original HTML files are present in `app/src/main/assets/reference_ui/`; exact visual parity with those designs, completed server workflows, and patient safety approvals are not claimed.
