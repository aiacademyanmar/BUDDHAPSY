<?php
function careAppointment(int $id,array $u):array {
 $a=sql('SELECT a.id,a.patient_id,a.doctor_id,a.status,d.user_id AS doctor_user_id FROM appointments a JOIN doctors d ON d.id=a.doctor_id WHERE a.id=?',[$id])->fetch();
 if(!$a || ((int)$a['patient_id']!==(int)$u['id'] && (int)$a['doctor_user_id']!==(int)$u['id']))out(404,['error'=>'Appointment not found']);
 return $a;
}
function careConsent(array $a):bool {
 $c=sql('SELECT permitted FROM care_consents WHERE appointment_id=? AND patient_id=?',[$a['id'],$a['patient_id']])->fetch();
 return $c && (int)$c['permitted']===1;
}
