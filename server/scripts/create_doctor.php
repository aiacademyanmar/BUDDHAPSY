<?php
require dirname(__DIR__).'/src/bootstrap.php';
if(PHP_SAPI!=='cli')exit(1);
if($argc!==7){fwrite(STDERR,"Usage: php create_doctor.php email password name specialty license_no issuer\n");exit(1);}
[$file,$email,$password,$name,$specialty,$license,$issuer]=$argv;
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<12)exit("Invalid credentials\n");
$db=db();$db->beginTransaction();try{
 sql('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT),'doctor']);$uid=(int)$db->lastInsertId();
 sql('INSERT INTO doctors(user_id,display_name,specialty,license_number,license_issuer) VALUES(?,?,?,?,?)',[$uid,$name,$specialty,$license,$issuer]);$db->commit();echo "Doctor created (UNVERIFIED).\n";
}catch(Throwable $e){$db->rollBack();fwrite(STDERR,"Failed: ".$e->getMessage()."\n");exit(1);}
