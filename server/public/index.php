<?php
declare(strict_types=1);
require dirname(__DIR__).'/src/bootstrap.php';
header('X-Content-Type-Options: nosniff');header('Referrer-Policy: no-referrer');header('X-Frame-Options: DENY');
header('Cache-Control: no-store, private');
// Web server must terminate TLS and set HTTPS=on when exposing APIs. Do not trust user-supplied X-Forwarded-Proto.
if(in_array(envv('APP_ENV','production'),['production','staging'],true) && !in_array(strtolower((string)($_SERVER['HTTPS']??'')),['on','1'],true)) {
  out(426,['error'=>'HTTPS is required']);
}
if(in_array(strtolower((string)($_SERVER['HTTPS']??'')),['on','1'],true))header('Strict-Transport-Security: max-age=31536000');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$path=preg_replace('#^/api#','',$path);
$method=$_SERVER['REQUEST_METHOD'];
require dirname(__DIR__).'/src/v12_helpers.php';
require dirname(__DIR__).'/src/v13_helpers.php';
try {
 require dirname(__DIR__).'/src/v12_routes.php';
 require dirname(__DIR__).'/src/v13_routes.php';
 require dirname(__DIR__).'/src/v14_routes.php';
 require dirname(__DIR__).'/src/v17_routes.php';
 require dirname(__DIR__).'/src/v171_routes.php';
 if($method==='GET'&&$path==='/health'){ try{db()->query('SELECT 1')->fetchColumn();out(200,['ok'=>true,'version'=>'1.7.2','clinical_ready'=>false]);}catch(Throwable $e){out(503,['ok'=>false,'error'=>'Service unavailable']);} }
 if($method==='POST'&&$path==='/auth/register'){
  $p=payload();if(($p['consent']??false)!==true)out(422,['error'=>'Privacy consent required']);
  $name=textField($p,'name');$email=strtolower(textField($p,'email',190));$pw=(string)($p['password']??'');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pw)<12||strlen($pw)>128)out(422,['error'=>'Valid email and 12-128 character password required']);
  if(sql('SELECT id FROM users WHERE email=?',[$email])->fetch())out(409,['error'=>'Email already registered']);
  sql('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)',[$name,$email,password_hash($pw,PASSWORD_DEFAULT)]);$id=(int)db()->lastInsertId();
  sql('INSERT INTO consent_records(user_id,consent_type,version) VALUES(?,?,?)',[$id,'privacy_registration','1.1']);audit($id,'patient_registered','user',$id);out(201,['data'=>['id'=>$id]]);
 }
 if($method==='POST'&&$path==='/auth/login'){
  $p=payload();$email=strtolower(trim((string)($p['email']??'')));
  if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || strlen((string)($p['password']??''))>128) out(401,['error'=>'Invalid credentials']);
  if (in_array(envv('APP_ENV','production'),['production','staging'],true) && strlen(envv('RATE_LIMIT_PEPPER'))<32)out(503,['error'=>'Authentication configuration incomplete']);
  $emailDigest=hash('sha256',$email);$ipDigest=hash('sha256',($_SERVER['REMOTE_ADDR']??'unknown').envv('RATE_LIMIT_PEPPER'));
  $attemptCount=(int)sql('SELECT COUNT(*) FROM login_attempts WHERE (email_digest=? OR ip_digest=?) AND attempted_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE)',[$emailDigest,$ipDigest])->fetchColumn();
  if($attemptCount>=10)out(429,['error'=>'Too many attempts; retry later']);
  sql('INSERT INTO login_attempts(email_digest,ip_digest) VALUES(?,?)',[$emailDigest,$ipDigest]);
  $u=sql('SELECT * FROM users WHERE email=?',[$email])->fetch();
  if(!$u||!$u['is_active']||!password_verify((string)($p['password']??''),$u['password_hash'])){usleep(250000);out(401,['error'=>'Invalid credentials']);}
  $token=bin2hex(random_bytes(32));sql('INSERT INTO api_tokens(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 7 DAY))',[$u['id'],hash('sha256',$token)]);
  sql('DELETE FROM login_attempts WHERE email_digest=?',[$emailDigest]);audit((int)$u['id'],'login');out(200,['token'=>$token,'user'=>['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']]]);
 }
 if($method==='POST'&&$path==='/auth/logout'){
  $u=auth();$h=$_SERVER['HTTP_AUTHORIZATION'];$raw=substr($h,7);sql('DELETE FROM api_tokens WHERE token_hash=?',[hash('sha256',$raw)]);audit((int)$u['id'],'logout');out(200,['ok'=>true]);
 }
 if($method==='GET'&&$path==='/doctors'){
  $rows=sql('SELECT id,display_name,specialty,verified FROM doctors WHERE verified=1 ORDER BY display_name LIMIT 100')->fetchAll();out(200,['data'=>$rows]);
 }
 if($method==='GET'&&$path==='/appointments'){
  $u=auth();$rows=sql('SELECT a.id,a.doctor_id,a.starts_at,a.modality,a.status,d.display_name FROM appointments a JOIN doctors d ON a.doctor_id=d.id WHERE a.patient_id=? ORDER BY a.created_at DESC LIMIT 100',[$u['id']])->fetchAll();out(200,['data'=>$rows]);
 }
 if($method==='POST'&&$path==='/appointments'){
  $u=auth('patient');$p=payload();$doc=(int)($p['doctor_id']??0);$starts=(string)($p['starts_at']??'');$modality=(string)($p['modality']??'');$reason=trim((string)($p['reason']??''));
  $date=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$starts,new DateTimeZone('Asia/Yangon'));
  if(!$date||$date->format('Y-m-d H:i:s')!==$starts||$date<=new DateTimeImmutable('now',new DateTimeZone('Asia/Yangon'))||!in_array($modality,['online','in_person'],true)||mb_strlen($reason)>500)out(422,['error'=>'Invalid booking details']);
  if(!sql('SELECT id FROM doctors WHERE id=? AND verified=1',[$doc])->fetch())out(422,['error'=>'Doctor is not verified']);
  $slot=sql('SELECT id FROM doctor_slots WHERE doctor_id=? AND starts_at=? AND modality=? AND is_active=1',[$doc,$starts,$modality])->fetch();
  if(!$slot)out(422,['error'=>'Slot unavailable; select a listed time']);
  try { sql('INSERT INTO appointments(patient_id,doctor_id,starts_at,modality,reason,slot_id) VALUES(?,?,?,?,?,?)',[$u['id'],$doc,$starts,$modality,$reason,$slot['id']]); }
  catch(PDOException $e){if($e->getCode()==='23000')out(409,['error'=>'Slot already booked']);throw $e;}$id=(int)db()->lastInsertId();audit((int)$u['id'],'appointment_requested','appointment',$id);out(201,['data'=>['id'=>$id,'status'=>'requested']]);
 }
 if($method==='GET'&&$path==='/admin/stats'){
  auth('admin');$result=[];foreach(['users','doctors','appointments'] as $t)$result[$t]=(int)db()->query('SELECT COUNT(*) FROM '.$t)->fetchColumn();out(200,['data'=>$result]);
 }
 if($method==='GET'&&$path==='/admin/doctors'){
  auth('admin');out(200,['data'=>sql('SELECT id,display_name,specialty,license_issuer,license_number,verified FROM doctors ORDER BY id DESC LIMIT 100')->fetchAll()]);
 }
 if($method==='POST'&&preg_match('#^/admin/doctors/([0-9]+)/verify$#',$path,$m)){
  if(envv('ALLOW_MANUAL_CREDENTIAL_ACTIVATION','false')!=='true')out(403,['error'=>'Credential verification disabled until independent source verification and governance approval']);
  $a=auth('admin');$id=(int)$m[1];$d=sql('SELECT id FROM doctors WHERE id=?',[$id])->fetch();if(!$d)out(404,['error'=>'Doctor not found']);
  // IMPORTANT: admin must independently verify credentials outside the software before approval.
  sql('UPDATE doctors SET verified=1,verified_by=?,verified_at=UTC_TIMESTAMP() WHERE id=?',[$a['id'],$id]);audit((int)$a['id'],'doctor_verified','doctor',$id);out(200,['ok'=>true]);
 }
 if($method==='GET'&&$path==='/admin/appointments'){
  auth('admin');out(200,['data'=>sql('SELECT a.id,a.starts_at,a.modality,a.status,d.display_name FROM appointments a JOIN doctors d ON a.doctor_id=d.id ORDER BY a.created_at DESC LIMIT 100')->fetchAll()]);
 }
 if($method==='POST'&&preg_match('#^/admin/appointments/([0-9]+)/status$#',$path,$m)){
  $a=auth('admin');$p=payload();$s=(string)($p['status']??'');if(!in_array($s,['confirmed','cancelled','completed'],true))out(422,['error'=>'Invalid status']);$id=(int)$m[1];$q=sql('UPDATE appointments SET status=? WHERE id=?',[$s,$id]);if(!$q->rowCount())out(404,['error'=>'Appointment not found or unchanged']);notifyPatient($id,'appointment_'.$s,'Your appointment status: '.$s);audit((int)$a['id'],'appointment_status_'.$s,'appointment',$id);out(200,['ok'=>true]);
 }
 out(404,['error'=>'Route not found']);
} catch(Throwable $e){error_log('Care API internal exception: '.get_class($e));out(500,['error'=>'Server error']);}
