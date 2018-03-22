<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$response = ['success' => false, 'errorMsg' => ''];

try {
    if (!isset($_GET['patientId'])) {
        throw new Exception('One or more required parameters was not set.', 1);
    }elseif(!preg_match( '/^-?[0-9]+$/', $_GET['patientId']) || $_GET['patientId'] <=0){
        throw new Exception('The parameter patient id should be integer.', 1);
    }
    
    $response['success'] = true;
    $response['results'] = searchPatientsbyId($_GET['patientId'], $mysqli);
}
catch(Exception $e) {
   echo  $response['errorMsg'] = $e->getMessage();
}

header('Content-Type: application/json');
echo(json_encode($response,JSON_NUMERIC_CHECK));
exit();
?>
