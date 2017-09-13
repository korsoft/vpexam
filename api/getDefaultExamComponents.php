<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$response = ['success' => false, 'errorMsg' => ''];

try {
    if (!isset($_GET['physId'])) {
        throw new Exception('One or more required parameters was not set.', 1);
    }
    $physId   = $_GET['physId'];
    $ecString = [];

    $prepStmtGetECString = "SELECT exam_components FROM physician_prefs WHERE id = ?";
    $stmtGetECString = $mysqli->prepare($prepStmtGetECString);
    if (!$stmtGetECString) {
        throw new Exception('Error preparing MySQL insert statement.', 2);
    }
    $stmtGetECString->bind_param('i', $physId);
    $stmtGetECString->execute();
    $stmtGetECString->bind_result($ecString);
    $stmtGetECString->fetch();
    $stmtGetECString->close();
    
    if(!empty($_GET['patientId']) && is_numeric($_GET['patientId'])) {
        $exam = getPatientExam($mysqli, $_GET['patientId'], $physId);
        if($exam['success']) {
            $response['exams'] = $exam['data'];
        }
        
    }
    $response['success']        = true;
    $response['examComponents'] = explode(',', str_replace(['[', ']', '"'], '', $ecString));

}
catch(Exception $e) {
    $response['errorMsg'] = $e->getMessage();
}
header('Content-Type: application/json');
echo(json_encode($response));
exit();