package asia.aaii.buddhapsychologycare

import androidx.compose.foundation.*
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import org.json.JSONArray
import org.json.JSONObject
import java.time.LocalDate

private fun JSONObject.arr(key:String):List<JSONObject> { val a=optJSONArray(key)?:JSONArray();return (0 until a.length()).mapNotNull { a.optJSONObject(it) } }
private fun JSONObject.errorText():String = optString("error", "Server response received")

@Composable fun CareApplication() {
    var chosen by remember { mutableIntStateOf(1) }
    var menu by remember { mutableStateOf(false) }
    var token by remember { mutableStateOf("") }
    var username by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var status by remember { mutableStateOf("Offline shell ready. Server connection unverified.") }
    var doctors by remember { mutableStateOf(listOf<JSONObject>()) }
    var appointments by remember { mutableStateOf(listOf<JSONObject>()) }
    var catalog by remember { mutableStateOf(listOf<JSONObject>()) }
    var consent by remember { mutableStateOf(mapOf("telehealth" to false,"care_share" to false,"notifications" to false,"research" to false)) }
    var consentPolicies by remember { mutableStateOf(mapOf<String, JSONObject>()) }
    var doctorId by remember { mutableStateOf("") }
    var bookingDate by remember { mutableStateOf(LocalDate.now().plusDays(3).toString()) }
    var bookingTime by remember { mutableStateOf("10:00") }
    var introQuestion by remember { mutableStateOf("") }
    var note by remember { mutableStateOf("") }
    var studyDone by remember { mutableStateOf(setOf<Int>()) }
    val page=carePages.first { it.number==chosen }
    fun goto(n:Int){ chosen=n;menu=false;status="Page ${"%02d".format(n)} — ${carePages[n-1].live}" }
    fun loadDoctors(){ CareApi.request("/doctors") { r -> doctors=r.arr("data");status=if(r.has("data")) "${doctors.size} server verified doctor(s)" else r.errorText() } }
    fun loadAppointments(){ if(token.isEmpty()){status="Please sign in first";return};CareApi.request("/appointments",bearer=token){r->appointments=r.arr("data");status=if(r.has("data"))"${appointments.size} appointment(s) returned" else r.errorText()} }
    CareTheme {
        Scaffold(containerColor=Cream,
            topBar={ Surface(color=Forest){ Row(Modifier.fillMaxWidth().padding(16.dp),verticalAlignment=Alignment.CenterVertically){
                Text("✿",color=Gold,style=MaterialTheme.typography.headlineLarge)
                Spacer(Modifier.width(10.dp));Column(Modifier.weight(1f)){Text("Buddha Psychology Care",color=Color.White,fontWeight=FontWeight.Bold);Text("V1.7.2 · CI TEST CANDIDATE",color=Color(0xFFE2C989),style=MaterialTheme.typography.labelSmall)}
                TextButton(onClick={menu=!menu}) {Text(if(menu)"Close" else "☰",color=Color.White)}
            } } },
            bottomBar={NavigationBar(containerColor=Color.White){listOf(1,2,3,11,20).forEach{n->NavigationBarItem(selected=chosen==n,onClick={goto(n)},icon={Text(carePages[n-1].icon)},label={Text(listOf("Home","Learn","Doctor","Wellness","More")[listOf(1,2,3,11,20).indexOf(n)])})}}}) { insets ->
            Column(Modifier.fillMaxSize().padding(insets)) {
                if(menu){ LazyColumn(Modifier.fillMaxWidth().weight(1f),contentPadding=PaddingValues(12.dp),verticalArrangement=Arrangement.spacedBy(5.dp)) {
                   items(carePages){ p-> OutlinedCard(onClick={goto(p.number)},modifier=Modifier.fillMaxWidth()){ Row(Modifier.padding(12.dp),verticalAlignment=Alignment.CenterVertically){Text("${p.number.toString().padStart(2,'0')}  ${p.icon}",color=Emerald);Spacer(Modifier.width(12.dp));Column(Modifier.weight(1f)){Text(p.title,fontWeight=FontWeight.SemiBold);Text(p.myanmar,style=MaterialTheme.typography.labelSmall)};Text(p.live,style=MaterialTheme.typography.labelSmall,color=Gold)} } }
                } } else {
                  LazyColumn(Modifier.fillMaxSize(),contentPadding=PaddingValues(16.dp),verticalArrangement=Arrangement.spacedBy(12.dp)) {
                    item {
                      Card(colors=CardDefaults.cardColors(containerColor=Forest),shape=RoundedCornerShape(20.dp)) {
                        Column(Modifier.fillMaxWidth().padding(20.dp),verticalArrangement=Arrangement.spacedBy(8.dp)) {
                          Text("PAGE ${page.number.toString().padStart(2,'0')} / 20",color=Gold,style=MaterialTheme.typography.labelMedium)
                          Text(page.title,color=Color.White,style=MaterialTheme.typography.headlineSmall,fontWeight=FontWeight.Bold)
                          Text(page.myanmar,color=Color(0xFFE1EFCF))
                          Text(page.intro,color=Color(0xFFD6E8DD))
                          Surface(color=Color(0xFF295D4A),shape=RoundedCornerShape(40.dp)) {Text("  ${page.live}   •   No clinical AI or emergency care  ",Modifier.padding(6.dp),color=Color(0xFFFFE1A2),style=MaterialTheme.typography.labelSmall)}
                        }
                      }
                    }
                    item { Card(colors=CardDefaults.cardColors(containerColor=Leaf)){ Text(status,Modifier.padding(14.dp),color=Forest,style=MaterialTheme.typography.bodySmall) } }
                    if(chosen==1) {
                      item { SectionHeading("20 native screens available") }
                      items(carePages.chunked(2)){row-> Row(horizontalArrangement=Arrangement.spacedBy(8.dp)){row.forEach{p->Card(onClick={goto(p.number)},modifier=Modifier.weight(1f),colors=CardDefaults.cardColors(containerColor=Color.White)){Column(Modifier.padding(12.dp)){Text("${p.icon} ${p.number.toString().padStart(2,'0')}",color=Gold);Text(p.title,style=MaterialTheme.typography.labelLarge,color=Forest);Text(p.live,style=MaterialTheme.typography.labelSmall,color=Emerald)}}};if(row.size==1)Spacer(Modifier.weight(1f))} }
                    } else {
                      item { SectionHeading("Module functions") }
                      items(page.functions){ f->Card(colors=CardDefaults.cardColors(containerColor=Color.White)){Row(Modifier.fillMaxWidth().padding(14.dp),verticalAlignment=Alignment.CenterVertically){Text("✓",color=Emerald);Spacer(Modifier.width(9.dp));Text(f,color=Forest)}} }
                    }
                    when(chosen) {
                      2,4,6,7,8,9,10 -> { item { Button(onClick={CareApi.request("/v17/catalog"){r->catalog=r.arr("courses")+r.arr("media")+r.arr("events");status=if(r.optBoolean("ok"))"${catalog.size} published catalog items received" else r.errorText()}}){Text("Fetch published learning catalog") } };items(catalog){o->Card { Column(Modifier.fillMaxWidth().padding(12.dp)){Text(o.optString("title","Media Item"),fontWeight=FontWeight.Medium);Text(o.optString("kind", "Catalog"),color=Emerald)} } } }
                      3,12 -> {item {Button(onClick={loadDoctors()}){Text("Load VERIFIED doctors (API)")}};items(doctors){d->OutlinedCard(onClick={doctorId=d.optString("id");goto(5)},modifier=Modifier.fillMaxWidth()){Column(Modifier.padding(13.dp)){Text(d.optString("display_name"),fontWeight=FontWeight.Bold);Text(d.optString("specialty"));Text("ID ${d.optString("id")} • Request booking",color=Emerald)}}};item{if(doctors.isEmpty())Text("No verified doctors loaded — directory must use real credential review.",color=Gold)} }
                      5 -> {item {SectionHeading("Appointment booking via server")};item{OutlinedTextField(doctorId,{doctorId=it.filter{c->c.isDigit()}},label={Text("Verified doctor ID")},modifier=Modifier.fillMaxWidth())};item{OutlinedTextField(bookingDate,{bookingDate=it},label={Text("Date YYYY-MM-DD")},modifier=Modifier.fillMaxWidth())};item{OutlinedTextField(bookingTime,{bookingTime=it},label={Text("Time HH:mm")},modifier=Modifier.fillMaxWidth())};item {Button(enabled=token.isNotBlank()&&doctorId.isNotBlank(),onClick={val body=JSONObject().put("doctor_id",doctorId.toIntOrNull()?:0).put("starts_at","$bookingDate $bookingTime:00").put("modality","online").put("reason", "");CareApi.request("/appointments","POST",token,body){r->status=if(r.has("data"))"Booking submitted to API" else r.errorText()}}){Text("Request actual booking (requires server slot)")}};item{OutlinedButton(enabled=token.isNotBlank(),onClick={loadAppointments()}){Text("Refresh my appointments")}};items(appointments){a->Card { Column(Modifier.fillMaxWidth().padding(10.dp)){Text(a.optString("display_name"));Text("${a.optString("starts_at")} · ${a.optString("status")}")}}} }
                      11 -> {item{SectionHeading("Offline self-study — not AI diagnosis")};item{OutlinedTextField(introQuestion,{introQuestion=it.take(350)},label={Text("General wellbeing / study question")},modifier=Modifier.fillMaxWidth())};item{ Button(onClick={status=if(Regex("suicid|self.harm|သတ်သေ|သေချင်",RegexOption.IGNORE_CASE).containsMatchIn(introQuestion))"For immediate danger seek local emergency services or the nearest emergency department; this app cannot respond." else "Try gentle mindful breathing and seek a qualified professional for persistent distress. This is scripted offline guidance."}){Text("Show educational tip")}};item{OutlinedTextField(note,{note=it.take(400)},label={Text("Local note (not saved)")},modifier=Modifier.fillMaxWidth())};item{Button(onClick={note=""}){Text("Clear private note")}} }
                      13,19 -> {item{SectionHeading("Purpose-specific consent (server ledger)")};items(consent.keys.toList()){key->
                          val policy=consentPolicies[key]
                          val active=policy?.optInt("is_active")==1
                          val current=consent[key]?:false
                          Card(colors=CardDefaults.cardColors(containerColor=Color.White)) {
                             Column(Modifier.fillMaxWidth().padding(10.dp)) {
                                Row(verticalAlignment=Alignment.CenterVertically) {
                                  Checkbox(checked=current,enabled=token.isNotBlank()&&(current||active),onCheckedChange={granted->
                                    val v=policy?.optString("policy_version").orEmpty()
                                    CareApi.request("/v171/consents","POST",token,
                                       JSONObject().put("purpose",key).put("granted",granted).put("version",v)) {r->
                                         if(r.optBoolean("ok")){consent=consent+(key to granted);status="Consent event saved by server"}else status=r.errorText()
                                       }
                                  })
                                  Text(key,color=Forest)
                                }
                                Text(policy?.optString("summary") ?: "Policy not retrieved. Grant unavailable.",style=MaterialTheme.typography.bodySmall,color=Emerald)
                                Text(if(active) "Policy v${policy?.optString("policy_version")} — review approved legal text before consenting" else "Grant DISABLED pending reviewed policy publication. Revocation remains available.",style=MaterialTheme.typography.labelSmall,color=Gold)
                             }
                          }
                        };item{OutlinedButton(enabled=token.isNotBlank(),onClick={
                          CareApi.request("/v171/consent-policies",bearer=token){r->
                            if(r.optBoolean("ok")){consentPolicies=r.arr("data").associateBy{it.optString("purpose")};status="Purpose policies retrieved (not legal approval)"}else status=r.errorText()
                          }
                          CareApi.request("/v171/consents",bearer=token){r->
                            if(r.optBoolean("ok")){consent=r.arr("data").associate{it.optString("purpose") to (it.optInt("granted")==1)}+consent.filterKeys{k->!r.arr("data").any{it.optString("purpose")==k}};status="Consent ledger refreshed"}else status=r.errorText()
                          }
                        }){Text("Load Policies & Consent from API")}} }
                      14,16 -> {item{SectionHeading("Sample educational follow-up checklist")};items((1..4).toList()){i->Card { Row(Modifier.fillMaxWidth().padding(8.dp),verticalAlignment=Alignment.CenterVertically){Checkbox(checked=i in studyDone,onCheckedChange={studyDone=if(it)studyDone+i else studyDone-i});Text(listOf("Review learning notes","Write questions","Mindfulness reflection","Review follow-up")[i-1])} }} }
                      20 -> {item {Button(onClick={CareApi.request("/v17/health"){r->status=if(r.optBoolean("ok"))"API responded: v${r.optString("version")}" else r.errorText()}}){Text("Ping actual API (HTTPS)")}};item{Text("Go-live remains blocked until clinical verification, security tests, device QA, data protection review and signed approval.",color=Color(0xFF9D522A))} }
                      else -> {item{Card(colors=CardDefaults.cardColors(containerColor=Color(0xFFF8EFDC))){Text("Module design shell ready; live backend workflow is NOT connected in V1.7 foundation. See integration matrix and next-stage plan.",Modifier.padding(14.dp),color=Color(0xFF785D34))}}}
                    }
                    if(chosen==1 || chosen==20){item{HorizontalDivider();SectionHeading("Server sign-in / စနစ်ဝင်ရန်")};item { OutlinedTextField(email,{email=it.trim()},label={Text("Email")},modifier=Modifier.fillMaxWidth())};item {OutlinedTextField(password,{password=it},label={Text("Password (never saved)")},modifier=Modifier.fillMaxWidth(),visualTransformation=androidx.compose.ui.text.input.PasswordVisualTransformation())};item{Row(horizontalArrangement=Arrangement.spacedBy(8.dp)){Button(enabled=email.isNotEmpty()&&password.isNotEmpty(),onClick={CareApi.request("/auth/login","POST",body=JSONObject().put("email",email).put("password",password)){r->if(r.has("token")){token=r.optString("token");username=r.optJSONObject("user")?.optString("name")?:"User";password="";status="Signed in: $username (memory-only session)"
                                    CareApi.request("/v171/consent-policies",bearer=token){p-> if(p.optBoolean("ok")) consentPolicies=p.arr("data").associateBy{it.optString("purpose")} }
                                    CareApi.request("/v171/consents",bearer=token){p-> if(p.optBoolean("ok")) consent=p.arr("data").associate{it.optString("purpose") to (it.optInt("granted")==1)}+consent.filterKeys{k->!p.arr("data").any{it.optString("purpose")==k}} }}else status=r.errorText()}}){Text("Log in")};OutlinedButton(enabled=token.isNotEmpty(),onClick={CareApi.request("/auth/logout","POST",token){token="";username="";consentPolicies=emptyMap();consent=consent.mapValues{false};status="Signed out; in-memory token cleared"}}){Text("Log out")}}} }
                    item {Text("Page ${page.number.toString().padStart(2,'0')}/20  •  V1.7.2 native Kotlin/Compose • Clinical features require professional review",style=MaterialTheme.typography.labelSmall,color=Gold)}
                  }
                }
             }
        }
    }
}

@Composable private fun SectionHeading(label:String){Text(label,style=MaterialTheme.typography.titleMedium,color=Forest,fontWeight=FontWeight.Bold)}
