<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
$success = false;
$errorMsg = "";

extract($_POST);

if (empty($idExCom)) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}    
elseif(!preg_match("/^[0-9]+$/",$idExCom))
{
    $errorMsg = "Please input numeric characters only.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}   
else
{
    $sql = "CALL sp_delete_exam_component(".$idExCom.");";    
    if ($resultUPD = $mysqli->query($sql)) {
        $objResult = $resultUPD->fetch_object();
        if(0<$objResult->idresult)
        {
            $success = true;
        }
        else
            $errorMsg = "Exam component not found.";
    }
    else {
        $errorMsg = "Error preparing mysql statement.";
    } 
}
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
?>