<?php
// V1.3 endpoints. Sensitive notes deliberately excluded from all general lists.
if ($method==='GET' && $path==='/me') {
 $u=auth();out(200,['data'=>['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']]]);
}
if ($method==='POST' && preg_match('#^/appointments/(\d+)/care-consent$#',$path,$m)) {
 $u=auth('patient');$id=(int)$m[1];$p=payload();
 if (!isset($p['permitted'])||!is_bool($p['permitted']))out(422,['error'=>'Boolean permitted required']);
 if (!sql('SELECT id FROM appointments WHERE id=? AND patient_id=?',[$id,$u['id']])->fetch())out(404,['error'=>'Appointment not found']);
 sql('INSERT INTO care_consents(patient_id,appointment_id,consent_version,permitted) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE permitted=VALUES(permitted),consent_version=VALUES(consent_version),recorded_at=CURRENT_TIMESTAMP',[$u['id'],$id,'1.3',intVal($p['permitted'])]);
 audit((int)$u['id'],$p['permitted']?'clinical_consent_granted':'clinical_consent_revoked','appointment',$id);out(200,['ok'=>true]);
}
if ($method==='GET'&&preg_match('#^/appointments/(\d+)/care-consent$#',$path,$m)) {
 $u=auth();$a=careAppointment((int)$m[1],$u);$c=sql('SELECT permitted,consent_version,recorded_at FROM care_consents WHERE appointment_id=?',[$a['id']])->fetch();out(200,['data'=>$c?:['permitted'=>0]]);
}
if ($method==='POST'&&preg_match('#^/doctor/appointments/(\d+)/notes$#',$path,$m)) {
 $u=auth('doctor');$a=careAppointment((int)$m[1],$u);$d=doctorSelf($u);
 if ((int)$a['doctor_id']!==(int)$d['id'])out(403,['error'=>'Not your appointment']);
 if (!careConsent($a))out(403,['error'=>'Patient clinical-note consent required']);
 if (!in_array($a['status'],['confirmed','completed'],true))out(409,['error'=>'Appointment not confirmed']);
 $p=payload();$note=trim((string)($p['note']??''));if(mb_strlen($note)<1||mb_strlen($note)>10000)out(422,['error'=>'Note must be 1-10000 characters']);
 sql('INSERT INTO clinical_notes(appointment_id,doctor_user_id,note_text) VALUES(?,?,?) ON DUPLICATE KEY UPDATE note_text=VALUES(note_text),updated_at=CURRENT_TIMESTAMP',[$a['id'],$u['id'],$note]);
 audit((int)$u['id'],'clinical_note_written','appointment',(int)$a['id']);out(200,['ok'=>true]);
}
if ($method==='GET'&&preg_match('#^/appointments/(\d+)/notes$#',$path,$m)) {
 $u=auth();$a=careAppointment((int)$m[1],$u);
 if (!careConsent($a))out(403,['error'=>'Clinical-note consent required']);
 $n=sql('SELECT note_text,created_at,updated_at FROM clinical_notes WHERE appointment_id=?',[$a['id']])->fetch();
 audit((int)$u['id'],'clinical_note_read','appointment',(int)$a['id']);out(200,['data'=>$n?:null]);
}
if ($method==='POST'&&preg_match('#^/doctor/appointments/(\d+)/followups$#',$path,$m)) {
 $u=auth('doctor');$a=careAppointment((int)$m[1],$u);
 if($a['status']!=='completed')out(409,['error'=>'Complete appointment before follow-up']);
 if(!careConsent($a))out(403,['error'=>'Patient consent required']);
 $p=payload();$guidance=trim((string)($p['guidance']??''));if(mb_strlen($guidance)<1||mb_strlen($guidance)>1000)out(422,['error'=>'Invalid follow-up']);
 sql('INSERT INTO followups(appointment_id,created_by,guidance) VALUES(?,?,?)',[$a['id'],$u['id'],$guidance]);
 notifyUser((int)$a['patient_id'],'care_followup','You have new follow-up guidance');
 audit((int)$u['id'],'followup_created','appointment',(int)$a['id']);out(201,['ok'=>true]);
}
if ($method==='GET'&&preg_match('#^/appointments/(\d+)/followups$#',$path,$m)) {
 $u=auth();$a=careAppointment((int)$m[1],$u);
 if(!careConsent($a))out(403,['error'=>'Patient consent required']);
 out(200,['data'=>sql('SELECT id,guidance,created_at FROM followups WHERE appointment_id=? ORDER BY id DESC',[$a['id']])->fetchAll()]);
}
