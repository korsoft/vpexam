<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";

if (!isset($_POST['physId'], $_POST['time'])) {
    $success = false;
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
} else {
    $physId = $_POST['physId'];
    $time = $_POST['time'];

    $prepStmtUpdateTime = "INSERT INTO physician_prefs(id, max_steth_record_time) VALUES(?, ?) ON DUPLICATE KEY UPDATE max_steth_record_time = VALUES(max_steth_record_time)";
    $stmtUpdateTime = $mysqli->prepare($prepStmtUpdateTime);
    if ($stmtUpdateTime) {
        $stmtUpdateTime->bind_param('ii', $physId, $time);
        $stmtUpdateTime->execute();
        $stmtUpdateTime->close();
    } else {
        $success = false;
        $error = "Error preparing mysql statement.";
        echo(json_encode(array("error" => $error, "success" => $success)));
        exit();
    }

    $success = true;
    $error = "";
    echo(json_encode(array("error" => $error, "success" => $success)));
    exit();
}
?>