<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

//$response = ['success' => false, 'errorMsg' => ''];
sec_session_start();

$success = false;
$errorMsg = "";

$physId = $_GET['physId'];
$patientId = $_GET['patientId'];
$examId = $_GET['examId'];
$documentId = $_GET['documentId'];

if (isset($physId) && isset($patientId) && isset($examId)  && isset($documentId)) {
    
    $mysqli->query("CALL sp_delete_exam_document({$_GET['documentId']});");
    
}else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing 3.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));