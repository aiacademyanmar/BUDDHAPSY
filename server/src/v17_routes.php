<?php
declare(strict_types=1);
// V1.7 API routes. Loaded before the existing final 404 handler.
if ($path === '/v17/health' && $method === 'GET') {
  try { db()->query('SELECT 1')->fetchColumn(); out(200,['ok'=>true,'version'=>'1.7.0','database'=>'reachable']); }
  catch (Throwable $e) { error_log('Health DB unavailable');out(503,['ok'=>false,'error'=>'Service unavailable']); }
}
if ($path === '/v17/catalog' && $method === 'GET') {
  $courses=sql("SELECT id,title,summary,'course' AS kind FROM learning_courses WHERE state='published' AND clinical_review_status='approved' ORDER BY id DESC LIMIT 40")->fetchAll();
  $media=sql("SELECT id,title,kind FROM media_catalog WHERE state='published' AND rights_status IN ('owned','licensed') ORDER BY id DESC LIMIT 40")->fetchAll();
  $events=sql("SELECT id,title,event_type AS kind,starts_at FROM community_events WHERE state='published' AND starts_at>UTC_TIMESTAMP() ORDER BY starts_at LIMIT 40")->fetchAll();
  out(200,['ok'=>true,'courses'=>$courses,'media'=>$media,'events'=>$events]);
}
if ($path === '/v17/me' && $method === 'GET') {
  $u=auth();out(200,['ok'=>true,'user'=>['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']]]);
}
if ($path === '/v17/consents' && $method === 'GET') {
  $u=auth();$data=sql('SELECT purpose,granted,document_version,changed_at FROM consent_status WHERE user_id=? ORDER BY purpose',[$u['id']])->fetchAll();
  out(200,['ok'=>true,'data'=>$data]);
}
if ($path === '/v17/consents' && $method === 'POST') {
  out(410,['error'=>'Legacy consent writes disabled. Update app to /v171/consents and review active policy version.']);
}
if ($path === '/v17/consent-history' && $method === 'GET') {
  $u=auth('patient');
  out(200,['ok'=>true,'data'=>sql('SELECT purpose,granted,document_version,event_at FROM consent_events WHERE user_id=? ORDER BY id DESC LIMIT 100',[$u['id']])->fetchAll()]);
}
if ($path === '/v17/admin/overview' && $method === 'GET') {
  $u=auth('admin');$stats=[];
  foreach(['users','doctors','appointments','learning_courses','media_catalog','community_events','consent_events','security_incident_drafts'] as $name){
    $stats[$name]=(int)db()->query('SELECT COUNT(*) FROM `'.$name.'`')->fetchColumn();
  }
  out(200,['ok'=>true,'stats'=>$stats,'unverified_doctors'=>(int)sql('SELECT COUNT(*) FROM doctors WHERE verified=0')->fetchColumn(),'release_drafts'=>(int)sql('SELECT COUNT(*) FROM admin_release_drafts')->fetchColumn()]);
}
if ($path === '/v17/admin/credential-queue' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,display_name,specialty,license_issuer,license_number,verified FROM doctors WHERE verified=0 ORDER BY id DESC LIMIT 50')->fetchAll(),
    'warning'=>'Pending verification; external source validation and documentary evidence required.']);
}
if ($path === '/v17/admin/audit' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,actor_id,event,target_type,target_id,created_at FROM audit_logs ORDER BY id DESC LIMIT 100')->fetchAll()]);
}
if ($path === '/v17/admin/releases' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,version_tag,state,created_by,created_at FROM admin_release_drafts ORDER BY id DESC LIMIT 100')->fetchAll()]);
}
if ($path === '/v17/admin/releases' && $method === 'POST') {
  $u=auth('admin');$p=payload();$tag=(string)($p['version_tag']??'');$notes=trim((string)($p['notes']??''));
  if(!preg_match('/^V?[0-9]+\.[0-9]+(\.[0-9]+)?[A-Za-z0-9._-]{0,28}$/',$tag)||mb_strlen($notes)>2000||mb_strlen($notes)<8)out(422,['error'=>'Invalid release draft']);
  try{sql('INSERT INTO admin_release_drafts(version_tag,notes,created_by) VALUES(?,?,?)',[$tag,$notes,$u['id']]);}
  catch(PDOException $e){if($e->getCode()==='23000')out(409,['error'=>'Release tag already exists']);throw $e;}
  audit((int)$u['id'],'release_draft_created','release',null);
  out(201,['ok'=>true,'state'=>'draft','warning'=>'This does not deploy, sign, or approve a release']);
}
if ($path === '/v17/admin/incidents' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,category,severity,state,created_at FROM security_incident_drafts ORDER BY id DESC LIMIT 100')->fetchAll()]);
}
if ($path === '/v17/admin/incidents' && $method === 'POST') {
  $u=auth('admin');$p=payload();$severity=(string)($p['severity']??'');$category=trim((string)($p['category']??''));$summary=trim((string)($p['summary']??''));
  if(!in_array($severity,['low','medium','high'],true)||mb_strlen($category)<3||mb_strlen($category)>60||mb_strlen($summary)<12||mb_strlen($summary)>1000)out(422,['error'=>'Invalid incident draft']);
  sql('INSERT INTO security_incident_drafts(category,severity,summary,created_by) VALUES(?,?,?,?)',[$category,$severity,$summary,$u['id']]);audit((int)$u['id'],'security_incident_draft_created','incident',null);
  out(201,['ok'=>true,'warning'=>'Draft only: no incident escalation or notifications']);
}

// Admin content drafts (not published, not medically approved, not licensed).
if ($path === '/v17/admin/courses' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,title,summary,state,clinical_review_status,created_at FROM learning_courses ORDER BY id DESC LIMIT 100')->fetchAll()]);
}
if ($path === '/v17/admin/courses' && $method === 'POST') {
  $u=auth('admin');$p=payload();$title=trim((string)($p['title']??''));$summary=trim((string)($p['summary']??''));
  if(mb_strlen($title)<4||mb_strlen($title)>180||mb_strlen($summary)>500)out(422,['error'=>'Invalid course draft']);
  sql('INSERT INTO learning_courses(title,summary,state,clinical_review_status) VALUES(?,?,?,?)',[$title,$summary,'draft','pending']);
  audit((int)$u['id'],'learning_course_draft_created','course',null);
  out(201,['ok'=>true,'state'=>'draft','clinical_review_status'=>'pending']);
}
if ($path === '/v17/admin/media' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,title,kind,rights_status,state,created_at FROM media_catalog ORDER BY id DESC LIMIT 100')->fetchAll()]);
}
if ($path === '/v17/admin/media' && $method === 'POST') {
  $u=auth('admin');$p=payload();$title=trim((string)($p['title']??''));$kind=(string)($p['kind']??'');
  if(mb_strlen($title)<4||mb_strlen($title)>180||!in_array($kind,['series_tv','audio_ebook','ebook'],true))out(422,['error'=>'Invalid media draft']);
  sql('INSERT INTO media_catalog(title,kind,state,rights_status) VALUES(?,?,?,?)',[$title,$kind,'draft','pending']);
  audit((int)$u['id'],'media_draft_created','media',null);
  out(201,['ok'=>true,'state'=>'draft','rights_status'=>'pending']);
}
if ($path === '/v17/admin/events' && $method === 'GET') {
  auth('admin');out(200,['ok'=>true,'data'=>sql('SELECT id,title,event_type,starts_at,state,capacity FROM community_events ORDER BY id DESC LIMIT 100')->fetchAll()]);
}
if ($path === '/v17/admin/events' && $method === 'POST') {
  $u=auth('admin');$p=payload();$title=trim((string)($p['title']??''));$eventType=(string)($p['event_type']??'');$when=(string)($p['starts_at']??'');
  $date=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$when,new DateTimeZone('UTC'));
  if(mb_strlen($title)<4||mb_strlen($title)>180||!in_array($eventType,['qa','workshop','meditation'],true)||!$date||$date->format('Y-m-d H:i:s')!==$when||$date<=new DateTimeImmutable('now',new DateTimeZone('UTC')))out(422,['error'=>'Invalid event draft']);
  sql('INSERT INTO community_events(title,event_type,starts_at,state) VALUES(?,?,?,?)',[$title,$eventType,$when,'draft']);
  audit((int)$u['id'],'event_draft_created','event',null);
  out(201,['ok'=>true,'state'=>'draft']);
}
