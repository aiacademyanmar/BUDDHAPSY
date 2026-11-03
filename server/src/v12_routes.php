<?php
// Loaded INSIDE the main try/catch; each route exits through out().
if($method==='GET'&&$path==='/doctors/slots') {
 $doctorId=(int)($_GET['doctor_id']??0);$date=(string)($_GET['date']??'');
 if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4)))out(422,['error'=>'Valid YYYY-MM-DD required']);
 if(!sql('SELECT id FROM doctors WHERE id=? AND verified=1',[$doctorId])->fetch())out(404,['error'=>'Doctor not found']);
 $rows=sql("SELECT s.id,s.starts_at,s.duration_minutes,s.modality FROM doctor_slots s LEFT JOIN appointments a ON a.doctor_id=s.doctor_id AND a.starts_at=s.starts_at AND a.status<>'cancelled' WHERE s.doctor_id=? AND s.is_active=1 AND s.starts_at>=? AND s.starts_at<DATE_ADD(?,INTERVAL 1 DAY) AND s.starts_at>CONVERT_TZ(UTC_TIMESTAMP(),'+00:00','+06:30') AND a.id IS NULL ORDER BY s.starts_at",[$doctorId,$date,$date])->fetchAll();
 out(200,['data'=>$rows,'timezone'=>'Asia/Yangon']);
}
if($method==='GET'&&$path==='/doctor/appointments') {
 $u=auth('doctor');$d=doctorSelf($u);
 // Reason deliberately excluded from general booking list.
 out(200,['data'=>sql('SELECT id,starts_at,modality,status FROM appointments WHERE doctor_id=? ORDER BY starts_at DESC LIMIT 100',[$d['id']])->fetchAll()]);
}
if($method==='GET'&&$path==='/doctor/slots') {
 $u=auth('doctor');$d=doctorSelf($u);
 out(200,['data'=>sql("SELECT id,starts_at,modality,duration_minutes,is_active FROM doctor_slots WHERE doctor_id=? AND starts_at>=CONVERT_TZ(UTC_TIMESTAMP(),'+00:00','+06:30') ORDER BY starts_at LIMIT 100",[$d['id']])->fetchAll()]);
}
if($method==='POST'&&$path==='/doctor/slots') {
 $u=auth('doctor');$d=doctorSelf($u);$p=payload();$starts=(string)($p['starts_at']??'');$modality=(string)($p['modality']??'');$duration=(int)($p['duration_minutes']??50);
 if(!validDateTime($starts)||!in_array($modality,['online','in_person'],true)||$duration<15||$duration>180)out(422,['error'=>'Invalid availability slot']);
 // Fixed-start conflict prevention; overlapping durations need a second business-level check before production use.
 try {sql('INSERT INTO doctor_slots(doctor_id,starts_at,modality,duration_minutes) VALUES(?,?,?,?)',[$d['id'],$starts,$modality,$duration]);}
 catch(PDOException $e){if($e->getCode()==='23000')out(409,['error'=>'Slot already exists']);throw $e;}
 $id=(int)db()->lastInsertId();audit((int)$u['id'],'doctor_slot_created','doctor_slot',$id);out(201,['data'=>['id'=>$id]]);
}
if($method==='POST'&&preg_match('#^/doctor/slots/(\d+)/deactivate$#',$path,$m)) {
 $u=auth('doctor');$d=doctorSelf($u);$id=(int)$m[1];
 $booked=sql("SELECT a.id FROM appointments a JOIN doctor_slots s ON s.id=a.slot_id WHERE s.id=? AND s.doctor_id=? AND a.status<>'cancelled' LIMIT 1",[$id,$d['id']])->fetch();
 if($booked)out(409,['error'=>'Booked slot cannot be deactivated; contact patient and admin']);
 $q=sql('UPDATE doctor_slots SET is_active=0 WHERE id=? AND doctor_id=? AND is_active=1',[$id,$d['id']]);
 if(!$q->rowCount())out(404,['error'=>'Slot not found']);audit((int)$u['id'],'doctor_slot_deactivated','doctor_slot',$id);out(200,['ok'=>true]);
}
if($method==='POST'&&preg_match('#^/appointments/(\d+)/cancel$#',$path,$m)) {
 $u=auth('patient');$id=(int)$m[1];
 $q=sql("UPDATE appointments SET status='cancelled' WHERE id=? AND patient_id=? AND status IN ('requested','confirmed')",[$id,$u['id']]);
 if(!$q->rowCount())out(409,['error'=>'Booking not found or cannot be cancelled']);audit((int)$u['id'],'appointment_cancelled','appointment',$id);notifyUser((int)$u['id'],'appointment_cancelled','Your appointment was cancelled');out(200,['ok'=>true]);
}
if($method==='GET'&&$path==='/notifications') {
 $u=auth();out(200,['data'=>sql('SELECT id,event_type,message,is_read,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 50',[$u['id']])->fetchAll()]);
}
if($method==='POST'&&preg_match('#^/notifications/(\d+)/read$#',$path,$m)) {
 $u=auth();sql('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?',[(int)$m[1],$u['id']]);out(200,['ok'=>true]);
}
