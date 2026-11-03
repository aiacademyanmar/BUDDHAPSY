package asia.aaii.buddhapsychologycare

/** Native screen registry aligned with the actual Page 01–20 HTML designs. */
data class CarePage(val number: Int, val title: String, val myanmar: String, val icon: String,
                    val intro: String, val functions: List<String>, val live: String)

val carePages = listOf(
 CarePage(1,"Home","ပင်မစာမျက်နှာ","⌂","Your learning and care hub",listOf("Quick access","Notifications","Wellness overview"),"Native"),
 CarePage(2,"Learning Academy","သင်ကြားရေး","▤","Learning catalog and course progress",listOf("Courses","Lessons","Practice"),"Preview"),
 CarePage(3,"Doctor Booking","ဆရာဝန်ရက်ချိန်း","✚","Verified directory and appointment request",listOf("Verified doctors","Available slots","Request booking"),"API"),
 CarePage(4,"Media & Community","မီဒီယာနှင့် အသိုင်းအဝိုင်း","▶","Education media and safe community",listOf("Psychology Series TV","Audio eBooks","Community"),"Preview"),
 CarePage(5,"Appointment Calendar","ရက်ချိန်းပြက္ခဒိန်","▦","Your actual booking records from server",listOf("Appointments","Statuses","Upcoming visits"),"API"),
 CarePage(6,"Media Content Manager","မီဒီယာစီမံခန့်ခွဲမှု","◫","Media publishing workflow specification",listOf("Draft media","Rights review","Sync plan"),"Preview"),
 CarePage(7,"Community Management","အသိုင်းအဝိုင်းစီမံခန့်ခွဲမှု","♧","Moderated learning community",listOf("Telegram","Member roles","Moderation"),"Preview"),
 CarePage(8,"Events & Live Learning","သင်တန်းနှင့် အစီအစဉ်များ","◷","Educational events & webinars",listOf("Live Q&A","Meditation events","Registration"),"Preview"),
 CarePage(9,"Live Learning Room","တိုက်ရိုက်သင်ခန်းစာ","▣","Live learning session foundation",listOf("Lesson materials","Attendance","Q&A"),"Preview"),
 CarePage(10,"Assessment & Certificate","စာမေးပွဲနှင့် လက်မှတ်","◇","Educational assessment model",listOf("Practice quiz","Progress","Certificate draft"),"Preview"),
 CarePage(11,"AI Wellness & Study Plan","AI စိတ်အေးချမ်းရေး","✦","Non-clinical self-study assistant",listOf("Mindfulness","Daily learning plan","Safety advice"),"Local"),
 CarePage(12,"Referral & Care Team","လွှဲပြောင်းကုသရေး","♡","Request a qualified professional",listOf("Doctor directory","Referral draft","Consent"),"Partial API"),
 CarePage(13,"Patient Records & Consent","လူနာမှတ်တမ်းနှင့် ခွင့်ပြုချက်","▤","Privacy-first record access",listOf("Patient profile","Purpose consent","Revocation"),"Partial API"),
 CarePage(14,"Care Progress & Follow-up","စောင့်ရှောက်မှုတိုးတက်ရေး","↗","Education progress and follow-up",listOf("Plan checklist","Upcoming visits","Progress view"),"Partial API"),
 CarePage(15,"Telehealth Consultation","အွန်လိုင်းကုသရေး","▻","Telehealth needs verified clinical infrastructure",listOf("Waiting room","Video/audio plan","Clinician approval"),"Not connected"),
 CarePage(16,"Consultation & Aftercare","ကုသပြီးနောက် စောင့်ရှောက်ရေး","▤","Care instructions and aftercare",listOf("Consultation summary","Aftercare","Clinical sign-off design"),"Not connected"),
 CarePage(17,"Clinical Quality & Outcomes","အရည်အသွေးနှင့် ရလဒ်","◉","Quality monitoring design",listOf("Review queue","Outcome metrics","Incident reports"),"Not connected"),
 CarePage(18,"Governance & Safety","အန္တရာယ်ကင်းရေးစီမံမှု","⚑","Clinical governance and controls",listOf("SOP","Doctor credentials","Safety audit"),"Not connected"),
 CarePage(19,"Security & Privacy","ဒေတာလုံခြုံရေး","▣","Consent and security architecture",listOf("Consent ledger","Role-based access","Security audit"),"Partial API"),
 CarePage(20,"Integration & Launch","စနစ်ပေါင်းစည်းနှင့် ဖြန့်ချိရေး","⇧","Release readiness with explicit gates",listOf("API health","Build checks","Go-live blockers"),"Preview")
)
