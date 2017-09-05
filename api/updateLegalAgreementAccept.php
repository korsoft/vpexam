<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../includes/psl-config.php';

sec_session_start();

const SIGNATURE_DIR = '/var/www/.uploads/legal/signatures/';

if (!isset($_POST['patientId'], $_POST['physId'], $_POST['accept'], $_POST['signature'], $_POST['date'])) {
    $success = false;
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
} else {
    $hospitalId = getHospitalId($_POST['physId'], $mysqli);
    if ($hospitalId === -1) {
        $success = false;
        $errorMsg = "Hospital ID fetch returned -1.";
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
        exit();
    }

    if (isset($_POST['signature']) && !isset($_POST['signatureData']))
        $_POST['signature'] = '0';
    else
        $signatureData = $_POST['signatureData'];

    $patientId = $_POST['patientId'];
    $physId = $_POST['physId'];
    $accept = $_POST['accept'];         // This script expects this value to be either 1 for true or 0 for false
    $signature = $_POST['signature'];   // This script expects this value to be either 1 for true or 0 for false
    $date = $_POST['date'];

    $prepStmtInsertAgree = "INSERT INTO legal_agreement_accept (patient_id, phys_id, hospital_id, accept, signature, date)
            VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            patient_id = VALUES(patient_id),
            phys_id = VALUES(phys_id),
            hospital_id = VALUES(hospital_id),
            accept = VALUES(accept),
            signature = VALUES(signature),
            date = VALUES(date)";
    $stmtInsertAgree = $mysqli->prepare($prepStmtInsertAgree);
    if ($stmtInsertAgree) {
        $stmtInsertAgree->bind_param('iiiiis', $patientId, $physId, $hospitalId, $accept, $signature, $date);
        $stmtInsertAgree->execute();
        $stmtInsertAgree->close();
    } else {
        $success = false;
        $errorMsg = "Error preparing .";
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
        exit();
    }

    // If the patient accepts the terms, and has attached a
    // signature, save it to disk.
    if ($accept && $signature) {
        // Filename format is as follows: patientId_physId_hospitalId.svg
        $filename = SIGNATURE_DIR . $patientId . '_' . $physId . '_' . $hospitalId . '.svg';
        file_put_contents($filename, $signatureData);
    }

    // Remove the signature if the patient no longer accepts the terms of
    // the agreement.
    if (!$accept) {
        $filename = SIGNATURE_DIR . $patientId . '_' . $physId . '_' . $hospitalId . '.svg';
        if (file_exists($filename))
            unlink($filename);
    }

    $success = true;
    $errorMsg = "";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
}

?>