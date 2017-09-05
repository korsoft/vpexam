<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";
$saveTime = "";

if (!isset($_GET["examId"], $_GET["physicianId"], $_GET["patientId"])) {
    $success = false;
    $error = "Missing parameters in query string.";
    $lastSaved = "";
    echo(json_encode(array("success" => $success, "error" => $error)));
    exit();
}

$prepStmtUpdateLastSaved = "REPLACE INTO clipboard_prefs SET physician_id = ?, patient_id = ?, exam_id = ?";
$stmtUpdateLastSaved = $mysqli->prepare($prepStmtUpdateLastSaved);
if ($stmtUpdateLastSaved) {
    $stmtUpdateLastSaved->bind_param('iii', $_GET["physicianId"], $_GET["patientId"], $_GET["examId"]);
    $stmtUpdateLastSaved->execute();
    $stmtUpdateLastSaved->close();
} else {
    $success = false;
    $error = "Error preparing MySQL statement.";
    $lastSaved = "";
    echo(json_encode(array("success" => $success, "error" => $error, "lastSaved" => $lastSaved)));
    exit();
}

$prepStmtGetLastSaved = "SELECT last_saved FROM clipboard_prefs WHERE physician_id = ? AND patient_id = ? AND exam_id = ?";
$stmtGetLastSaved = $mysqli->prepare($prepStmtGetLastSaved);
if ($stmtGetLastSaved) {
    $stmtGetLastSaved->bind_param('iii', $_GET["physicianId"], $_GET["patientId"], $_GET["examId"]);
    $stmtGetLastSaved->execute();
    $stmtGetLastSaved->bind_result($lastSaved);
    $stmtGetLastSaved->fetch();
    $stmtGetLastSaved->close();
} else {
    $success = false;
    $error = "Error preparing MySQL statement.";
    $lastSaved = "";
    echo(json_encode(array("success" => $success, "error" => $error, "lastSaved" => $lastSaved)));
    exit();
}

$success = true;
$error = "";
echo(json_encode(array("success" => $success, "error" => $error, "lastSaved" => $lastSaved)));
?>