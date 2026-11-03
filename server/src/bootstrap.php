<?php
declare(strict_types=1);
function envv(string $k,string $default=''):string { $v=getenv($k);return $v===false?$default:$v; }
function db():PDO { static $p=null;if($p)return $p;$p=new PDO('mysql:host='.envv('DB_HOST','127.0.0.1').';dbname='.envv('DB_NAME','buddha_care').';charset=utf8mb4',envv('DB_USER'),envv('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);return $p; }
function out(int $code,array $data):never { http_response_code($code);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode($data,JSON_UNESCAPED_UNICODE);exit; }
function payload():array { $s=file_get_contents('php://input');$v=json_decode($s,true);if(!is_array($v))out(400,['error'=>'Expected JSON request']);return $v; }
function sql(string $q,array $params=[]):PDOStatement { $s=db()->prepare($q);$s->execute($params);return $s; }
function auth(?string $role=null):array { $h=$_SERVER['HTTP_AUTHORIZATION']??'';if(!preg_match('/^Bearer ([a-f0-9]{64})$/',$h,$m))out(401,['error'=>'Authentication required']);$u=sql('SELECT u.id,u.name,u.email,u.role,u.is_active FROM api_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=? AND t.expires_at>UTC_TIMESTAMP()', [hash('sha256',$m[1])])->fetch();if(!$u||!$u['is_active'])out(401,['error'=>'Invalid or expired token']);if($role!==null&&$u['role']!==$role)out(403,['error'=>'Not allowed']);return $u; }
function audit(?int $id,string $event,?string $type=null,?int $target=null):void { sql('INSERT INTO audit_logs(actor_id,event,target_type,target_id) VALUES(?,?,?,?)',[$id,$event,$type,$target]); }
function textField(array $p,string $k,int $max=150):string {$v=trim((string)($p[$k]??''));if($v===''||mb_strlen($v)>$max)out(422,['error'=>'Invalid '.$k]);return $v;}
