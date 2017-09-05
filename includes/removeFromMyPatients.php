<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";

if (isset($_POST['physId'], $_POST['patientId'])) {
    $prepStmtSetNoDisplay = "INSERT INTO patients_no_display VALUES (?, ?)";
    $stmtSetNoDisplay = $mysqli->prepare($prepStmtSetNoDisplay);
    if ($stmtSetNoDisplay) {
        $stmtSetNoDisplay->bind_param('ii', $_POST['patientId'], $_POST['physId']);
        $stmtSetNoDisplay->execute();
        $stmtSetNoDisplay->close();

        $success = true;
        $error = "";
        echo(json_encode(array("success" => $success, "error" => $error)));
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "error" => $error)));
        exit();
    }
} else {
    $success = false;
    $error = "One or more required parameters is missing.";
    echo(json_encode(array("success" => $success, "error" => $error)));
}
?>