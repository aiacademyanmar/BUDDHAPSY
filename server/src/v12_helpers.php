<?php
declare(strict_types=1);
function notifyUser(int $userId,string $event,string $message):void {
 sql('INSERT INTO notifications(user_id,event_type,message) VALUES(?,?,?)',[$userId,$event,$message]);
}
function notifyPatient(int $appointmentId,string $event,string $message):void {
 $row=sql('SELECT patient_id FROM appointments WHERE id=?',[$appointmentId])->fetch();
 if($row)notifyUser((int)$row['patient_id'],$event,$message);
}
function validDateTime(string $input):bool {
 $tz=new DateTimeZone('Asia/Yangon');$d=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$input,$tz);
 return $d!==false && $d->format('Y-m-d H:i:s')===$input && $d>new DateTimeImmutable('now',$tz);
}
function doctorSelf(array $u):array {
 $d=sql('SELECT id,verified FROM doctors WHERE user_id=?',[$u['id']])->fetch();
 if(!$d||!(int)$d['verified'])out(403,['error'=>'Verified doctor required']);return $d;
}
