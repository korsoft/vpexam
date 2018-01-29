<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
$success = false;
$errorMsg = "";
$response = "";
extract($_POST);

if (empty($password) || 0==$patientid) {
    $errorMsg = "One or more required parameters was not set.".$patientid;
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
else
{
    $sql = "CALL sp_select_patient_password(".$patientid.");";    
    if ($resultUPD = $mysqli->query($sql)) {
        $objResult = $resultUPD->fetch_object();
        if(''!=$objResult->password)
        {
            $password = hash('sha512', $password . $objResult->salt);
            if ($objResult->password == $password) {
                $success = true;
            }
        }
        else {
            $errorMsg = "Error preparing mysql statement.";
        }        
    }
    else {
        $errorMsg = "-Error preparing mysql statement.";
    }
}
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
?>