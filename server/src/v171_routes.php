<?php
declare(strict_types=1);
// V1.7.1 policy-bound consent. Version checks are enforced on the server; revocation is always possible.
if ($path === '/v171/health' && $method === 'GET') {
  try {
    db()->query('SELECT 1')->fetchColumn();
    db()->query('SELECT COUNT(*) FROM consent_policy_versions')->fetchColumn();
    out(200,['ok'=>true,'version'=>'1.7.2','database'=>'reachable','clinical_ready'=>false]);
  } catch(Throwable $e) { error_log('V171 readiness unavailable');out(503,['ok'=>false,'error'=>'Staging database unavailable or migrations incomplete']); }
}
if ($path === '/v171/consent-policies' && $method === 'GET') {
  $u=auth('patient');
  out(200,['ok'=>true,'data'=>sql('SELECT purpose,policy_version,summary,document_url,document_sha256,is_active FROM consent_policy_versions ORDER BY purpose')->fetchAll(),
    'warning'=>'Unapproved policies are inactive. Review complete legal text before granting consent.']);
}
if ($path === '/v171/consents' && $method === 'GET') {
  $u=auth('patient');
  out(200,['ok'=>true,'data'=>sql('SELECT purpose,granted,document_version,changed_at FROM consent_status WHERE user_id=? ORDER BY purpose',[$u['id']])->fetchAll()]);
}
if ($path === '/v171/consents' && $method === 'POST') {
  $u=auth('patient');$p=payload();
  $purpose=(string)($p['purpose']??'');$granted=$p['granted']??null;
  $claimedVersion=(string)($p['version']??'');
  if(!in_array($purpose,['telehealth','care_share','notifications','research'],true)||!is_bool($granted))out(422,['error'=>'Invalid consent request']);
  $db=db();$db->beginTransaction();
  try {
    // Lock policy version while recording an event so it cannot change midway.
    $policy=sql('SELECT policy_version,is_active,document_url,document_sha256,reviewed_by,reviewed_at FROM consent_policy_versions WHERE purpose=? FOR UPDATE',[$purpose])->fetch();
    if(!$policy){$db->rollBack();out(503,['error'=>'Policy configuration not found']);}
    if($granted){
      if(!$policy['is_active']||!$policy['document_url']||!preg_match('#^https://#',$policy['document_url'])||!preg_match('/^[a-f0-9]{64}$/i',(string)$policy['document_sha256'])||!$policy['reviewed_by']||!$policy['reviewed_at']){
        $db->rollBack();out(403,['error'=>'Consent grants disabled until reviewed policy publication']);
      }
      if(!hash_equals((string)$policy['policy_version'],$claimedVersion)){
        $db->rollBack();out(409,['error'=>'Policy version changed; re-read the current policy']);
      }
      $version=$claimedVersion;
    } else {
      // A revocation must not be blocked by a version rollover or inactive policy.
      $previous=sql('SELECT document_version FROM consent_status WHERE user_id=? AND purpose=? FOR UPDATE',[$u['id'],$purpose])->fetch();
      $version=$previous['document_version']??(string)$policy['policy_version'];
    }
    sql('INSERT INTO consent_status(user_id,purpose,granted,document_version) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE granted=VALUES(granted),document_version=VALUES(document_version),changed_at=UTC_TIMESTAMP()',[(int)$u['id'],$purpose,$granted?1:0,$version]);
    sql('INSERT INTO consent_events(user_id,purpose,granted,document_version,origin) VALUES(?,?,?,?,?)',[(int)$u['id'],$purpose,$granted?1:0,$version,'android-v171']);
    audit((int)$u['id'],$granted?'consent_granted_v171':'consent_revoked_v171','consent',null);
    $db->commit();
  } catch(Throwable $e) { if($db->inTransaction())$db->rollBack();throw $e; }
  out(200,['ok'=>true,'purpose'=>$purpose,'granted'=>$granted,'document_version'=>$version]);
}
if ($path === '/v171/consent-history' && $method === 'GET') {
  $u=auth('patient');
  out(200,['ok'=>true,'data'=>sql('SELECT purpose,granted,document_version,origin,event_at FROM consent_events WHERE user_id=? ORDER BY id DESC LIMIT 100',[$u['id']])->fetchAll()]);
}
