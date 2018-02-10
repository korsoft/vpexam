<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$success = false;
$errorMsg = "";

$physId      	   = $_POST['physId'];
$emailnotification = $_POST['emailnotification'];

if (isset($emailnotification) && isset($physId) ) {
    $mysqli->query("CALL sp_update_email_notification({$physId},{$emailnotification});");
    error_log("::::::: llamo sp para cambiar settings CALL sp_update_email_notification({$physId},{$emailnotification}); ");
    
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