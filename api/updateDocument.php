<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$success = false;
$errorMsg = "";

$documentId = $_GET['documentId'];
$nameDocument = $_GET['nameDocument'];

if (isset($documentId) && isset($nameDocument)) {
    $mysqli->query("CALL sp_update_exam_document({$documentId},'{$nameDocument}');");
    
}else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));