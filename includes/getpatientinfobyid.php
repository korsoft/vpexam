<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
$success = false;
$errorMsg = "";
$response = "";
extract($_POST);

if (0==$patientid) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
else
{
    $sql = "CALL sp_select_patient_info(".$patientid.");";    
    if ($result = $mysqli->query($sql)) {
        $success = true;
        $response = $result->fetch_assoc();
    }
    else {
        $errorMsg = "Information for patient not found.";
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }    
}
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "data" => $response)));
?>