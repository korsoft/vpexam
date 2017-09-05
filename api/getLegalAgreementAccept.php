<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../includes/psl-config.php';

sec_session_start();

if (!isset($_GET['patientId'], $_GET['physId'])) {
    $errorMsg = "One or more required parameters is missing.";
    $success = false;
    $agreementAccepted = false;
    echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success, "agreementAccepted" => $agreementAccepted)));
    exit();
} else {
    $patientId = $_GET['patientId'];
    $physId = $_GET['physId'];

    $prepStmtCheck = "SELECT accept FROM legal_agreement_accept WHERE patient_id = ? AND phys_id = ?";
    $stmtCheck = $mysqli->prepare($prepStmtCheck);
    if ($stmtCheck) {
        $acceptIntVal = 0;
        $stmtCheck->bind_param('ii', $patientId, $physId);
        $stmtCheck->execute();
        $stmtCheck->bind_result($acceptIntVal);
        $stmtCheck->fetch();
        $stmtCheck->close();

        $errorMsg = "One or more required parameters is missing.";
        $success = true;
        $agreementAccepted = $acceptIntVal ? true : false;
        echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success, "agreementAccepted" => $agreementAccepted)));
    } else {
        $errorMsg = "Error preparing MySQL statement.";
        $success = false;
        $agreementAccepted = false;
        echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success, "agreementAccepted" => $agreementAccepted)));
        exit();
    }
}

?>