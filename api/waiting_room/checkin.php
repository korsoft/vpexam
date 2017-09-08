<?php
include_once '../../includes/db_connect.php';
include_once '../../includes/functions.php';

sec_session_start();

$response = [
	'success'  => true,
	'errorMsg' => ''
];

error_log(print_r($_POST, true));

$physicianid = isset($_POST['physician'])?$_POST['physician']:0;
$patientid   = !empty($_SESSION['user_id'])?$_SESSION['user_id']:0;
$patientname = 0 < $patientid?"{$_SESSION['first_name']} {$_SESSION['last_name']}":(isset($_POST['patientname'])?$_POST['patientname']:'');

try {
	if(0 >= $physicianid) {
		throw new Exception("Invalid physician id { $physicianid }", 1);
	}
	if('' == $patientname) {
		throw new Exception("Invalid patient name { $patientname }", 2);
	}
	if(0 >= $patientid) {
		error_log('API :: WAITING ROOM :: CHECK IN : User not logged, continue with a timesstamp value as provisional id.');
		$patientid = time();
	}
	$response = checkInWaitingRoom($mysqli, $physicianid, ['id' => $patientid, 'name' => $patientname]);
        include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/Mailer.php';
	$infoforemail       = getInfoForEmail($mysqli, $patientid, $physicianid);
        $infoforemail[0][0] = array(
                               'name'  => 'webmaster',
                               'email' => 'webmaster@vpexam.com',
                              );
        $mailer = new Mailer('waiting_room_patient', 'A new patient has checked into your waiting room.', $infoforemail[0][0], $infoforemail[1], [
            'url'        => 'https://vpexam.com/',
            'img_header' => 'https://vpexam.com/img/logo_img.png'
        ]);
        $mailer->send();
}
catch(Exception $e) {
 	$response['success']  = false;
	$response['errorMsg'] = $e->getMessage();
}
echo(json_encode($response));
exit();
