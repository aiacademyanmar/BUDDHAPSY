<?php
// V1.4 routes: date filtering, doctor requests, limited admin reports, patient profile.
if($method==='GET'&&$path==='/doctor/calendar') {
 $u=auth('doctor');$d=doctorSelf($u);
 $date=(string)($_GET['date']??'');
 if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4)))out(422,['error'=>'Valid date required']);
 $slots=sql('SELECT id,starts_at,duration_minutes,modality,is_active FROM doctor_slots WHERE doctor_id=? AND starts_at>=? AND starts_at<DATE_ADD(?,INTERVAL 1 DAY) ORDER BY starts_at',[$d['id'],$date,$date])->fetchAll();
 $bookings=sql('SELECT id,starts_at,modality,status,slot_id FROM appointments WHERE doctor_id=? AND starts_at>=? AND starts_at<DATE_ADD(?,INTERVAL 1 DAY) ORDER BY starts_at',[$d['id'],$date,$date])->fetchAll();
 out(200,['data'=>['slots'=>$slots,'appointments'=>$bookings],'timezone'=>'Asia/Yangon']);
}
if($method==='GET'&&$path==='/patient/calendar') {
 $u=auth('patient');$month=(string)($_GET['month']??'');
 if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$month))out(422,['error'=>'Valid YYYY-MM month required']);
 $rows=sql("SELECT a.id,a.starts_at,a.modality,a.status,d.display_name FROM appointments a JOIN doctors d ON a.doctor_id=d.id WHERE a.patient_id=? AND a.starts_at>=CONCAT(?,'-01') AND a.starts_at<DATE_ADD(CONCAT(?,'-01'),INTERVAL 1 MONTH) ORDER BY a.starts_at",[$u['id'],$month,$month])->fetchAll();
 out(200,['data'=>$rows,'timezone'=>'Asia/Yangon']);
}
if($method==='GET'&&$path==='/admin/reports/bookings') {
 auth('admin');$from=(string)($_GET['from']??'');$to=(string)($_GET['to']??'');
 foreach([$from,$to] as $v){if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)||!checkdate((int)substr($v,5,2),(int)substr($v,8,2),(int)substr($v,0,4)))out(422,['error'=>'Valid from/to dates required']);}
 if($from>$to||((strtotime($to)-strtotime($from))/86400)>366)out(422,['error'=>'Date range must be <= 366 days']);
 $summary=sql('SELECT status,COUNT(*) AS total FROM appointments WHERE starts_at>=? AND starts_at<DATE_ADD(?,INTERVAL 1 DAY) GROUP BY status',[$from,$to])->fetchAll();
 $modalities=sql('SELECT modality,COUNT(*) AS total FROM appointments WHERE starts_at>=? AND starts_at<DATE_ADD(?,INTERVAL 1 DAY) GROUP BY modality',[$from,$to])->fetchAll();
 out(200,['data'=>['statuses'=>$summary,'modalities'=>$modalities,'from'=>$from,'to'=>$to]]);
}
if($method==='GET'&&$path==='/consultations/capabilities') {
 $u=auth();out(200,['data'=>['video_enabled'=>false,'provider'=>'not_configured','message'=>'No video calls are available in this release. A vetted provider, short-lived credentials, and consent are required.']]);
}
