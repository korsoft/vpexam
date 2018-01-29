<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
$success = false;
$errorMsg = "";
$response = "";
extract($_POST);

$strUsername = preg_replace('/[^A-Za-z0-9\-]/', '', $strUsername);

if (empty($strUsername)) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}   
else
{
    $success = true;
    $sql = "CALL sp_select_patients_by_username('".$strUsername."');";    
   $response = array();
    if ($result = $mysqli->query($sql)) {
        while ($wrinfo = $result->fetch_assoc()) {
            $response[] = $wrinfo;
        }
    }
    else {
        $errorMsg = "Error preparing mysql statement.";
    } 
}
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $response)));
?>