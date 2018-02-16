<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";
extract($_POST);
if (empty($patientid)) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^[0-9]+$/",$patientid))
{
    $errorMsg = "Please input numeric characters only.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
else {
    $sql = "CALL sp_delete_patient_in_waitingroom(".$patientid.");";    
    if ($resultUPD = $mysqli->query($sql)) {
        $objResult = $resultUPD->fetch_object();
        if(0<$objResult->idresult)
        {
            $success = true;
        }
        else
            $errorMsg = "Patient not found.";
    }
    else {
        $errorMsg = "Error preparing mysql statement.";
    }
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
}
?>