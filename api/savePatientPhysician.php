<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";

extract($_POST);

if (empty($patientid) ||  empty($physicianid)) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^[0-9]+$/",$physicianid) ||  !preg_match("/^[0-9]+$/",$patientid))
{
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit(); 
}
elseif(0>=$physicianid ||  0>=$patientid)
{
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit(); 
}
else{
    
    $sql = "CALL sp_register_patient_physician({$patientid},{$physicianid});";
    if ($resultUPD = $mysqli->query($sql)) {
        $objResult = $resultUPD->fetch_object();
        if(0<$objResult->patient_id)
        {
            $success = true;
        }
        else {
           $errorMsg = "Error preparing mysql statement.";
        }        
    }
    else {
        $errorMsg = "Error preparing mysql statement.";
    } 
}
echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
exit(); 
?>