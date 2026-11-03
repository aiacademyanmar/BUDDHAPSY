<?php
require dirname(__DIR__).'/src/bootstrap.php';
if(PHP_SAPI!=='cli')exit(1);
if($argc!==3){fwrite(STDERR,"Usage: php create_admin.php email@example.com 'StrongPassword'\n");exit(1);}
$email=$argv[1];$password=$argv[2];if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<12)exit("Invalid email/password\n");
if(sql('SELECT id FROM users WHERE email=?',[$email])->fetch())exit("Account already exists\n");
sql('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)',['Care Admin',$email,password_hash($password,PASSWORD_DEFAULT),'admin']);echo "Admin created. Change password and enable MFA before production.\n";
