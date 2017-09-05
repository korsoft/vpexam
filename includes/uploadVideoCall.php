<?php
include_once 'db_connect.php';
include_once 'functions.php';

sec_session_start();

$response = [
    'success'  => true, 
    'errorMsg' => ''
];

error_log('***** _POST { ' . print_r($_POST, true) . ' } *****');
error_log('***** _FILES { ' . print_r($_FILES, true) . ' } *****');

try {
    if(empty($_POST['id'])) {
       throw new Exception('Invalid physician id. ', 1);
    }
    if(empty($_POST['otherid'])) {
       throw new Exception('Invalid patient id. ', 2);
    }
    if(empty($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
        throw new Exception('Missing video to save. ', 3);
    }
    $id          = 0;
    $physicianid = $_POST['id'];
    $patientid   = $_POST['otherid'];
    $results     = $mysqli->query("CALL dbcode.sp_add_patient_video_call($physicianid, $patientid, '" . (!empty($_POST['file_name'])?$_POST['file_name']:time()) . "');");
    $row         = $results->fetch_object();
    if(isset($row->id) && 0 < $row->id) {
        $upload_dir  = "/var/www/.uploads/{$physicianid}/{$patientid}/calls/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        move_uploaded_file($_FILES['file']['tmp_name'], "{$upload_dir}{$row->id}.webm");
    }
}
catch(Exception $e) {
    $response['success']  = false;
    $response['errorMsg'] = $e->getMessage(); 
}
echo(json_encode($response));
exit();
