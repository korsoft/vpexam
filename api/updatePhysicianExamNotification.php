<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$success = false;
$errorMsg = "";

$physId      	   = $_POST['physId'];
$examnotification = $_POST['examnotification'];

if (isset($examnotification) && isset($physId) ) {
    $mysqli->query("CALL sp_update_exam_notification({$physId},{$examnotification});");
    error_log("::::::: llamo sp para cambiar settings CALL sp_update_exam_notification({$physId},{$examnotification}); ");
    
}else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    error_log('::::::: Error al llamar sp para cambiar settings');
    exit();
}
$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));