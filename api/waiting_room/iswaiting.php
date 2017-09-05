<?php
include_once '../../includes/db_connect.php';
include_once '../../includes/functions.php';

sec_session_start();

$response = [
	'success'  => true,
	'errorMsg' => ''
];

$patientid = isset($_SESSION['user_id'])?$_SESSION['user_id']:(isset($_POST['patientid'])?$_POST['patientid']:0);

try {
	if(0 >= $patientid) {
		throw new Exception("Invalid patient id { $patientid }", 1);
	}
	$response = isInWaitingRoom($mysqli, $patientid);
}
catch(Exception $e) {
 $response['success']  = false;
 $response['errorMsg'] = $e->getMessage();
}
echo(json_encode($response));
exit();
