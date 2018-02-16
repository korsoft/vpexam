<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
$success = false;
$errorMsg = "";
$response = "";
extract($_POST);

if (0==$physicianid) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
else
{
    $sql = "CALL sp_select_patients_physician_waitingroom(".$physicianid.");";    
    if ($result = $mysqli->query($sql)) {
        $success = true;
        $response = array();
        while ($wrinfo = $result->fetch_assoc()) {
            $response[] = $wrinfo;
        }
    }
    else {
        $errorMsg = "Information for physician not found.";
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }    
}
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "data" => $response)));
?>