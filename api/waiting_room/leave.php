<?php
include_once '../../includes/db_connect.php';
include_once '../../includes/functions.php';

sec_session_start();

$response = [
	'success'  => true,
	'errorMsg' => ''
];

$patientid   = isset($_POST['patientid'])?$_POST['patientid']:(isset($_SESSION['user_id']) && isset($_SESSION['is_patient'])?$_SESSION['user_id']:0);
$physicianid = isset($_POST['physicianid'])?$_POST['physicianid']:0;

try {
	if(0 >= $patientid) {
		throw new Exception("Invalid patient id { $patientid }", 1);
	}
	if(0 >= $physicianid) {
		throw new Exception("Invalid physician id { $physicianid }", 2);
	}
	$response = leaveWaitingRoom($mysqli, $physicianid, $patientid);
}
catch(Exception $e) {
 $response['success']  = false;
 $response['errorMsg'] = $e->getMessage();
}
echo(json_encode($response));
exit();

