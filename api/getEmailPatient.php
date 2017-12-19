<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();


$email = $_GET['email'];



$success = false;
$errorMsg = "";

if (isset($email)) {
    //Call Stored Procedure
    $prepStmtCheckEmail = "CALL sp_select_email_physician_patient(?);";

    $stmtCheckEmail = $mysqli->prepare($prepStmtCheckEmail);
    if ($stmtCheckEmail) {
        $stmtCheckEmail->bind_param('s', $email);
        $stmtCheckEmail->execute();
        $result = $stmtCheckEmail->get_result();

        # fetch object
        $objResult = $result->fetch_object();
        if ($objResult->intTotal!=0) {
            //$stmtCheckEmail->close();
            $errorMsg = "The email already exists";
            $array = array(
                "errorMsg" => $errorMsg,
                "intTotal" => $objResult->intTotal
            );
            echo(json_encode(array("success" => $success, "result" => $array)));
        }else{
            $success = true;
             $array = array(
                "errorMsg" => $errorMsg,
                "intTotal" => $objResult->intTotal
            );
             echo(json_encode(array("success" => $success, "result" => $array)));
        }
        
        $stmtCheckEmail->close();
        return $array;
    } 

        
}else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
$success = true;
$errorMsg = "";
//echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
echo json_encode($array);