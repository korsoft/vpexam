<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

parse_str($_SERVER['QUERY_STRING']);

$success = false;
$errorMsg = "";
$text = "";

if (!isset($physId)) {
    $errorMsg = "Invalid parameters";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
    exit;
}

$prepStmtGetPhysNormal = "SELECT normal FROM physician_prefs WHERE id = ?";
$stmtGetPhysNormal = $mysqli->prepare($prepStmtGetPhysNormal);

if ($stmtGetPhysNormal) {
    $stmtGetPhysNormal->bind_param('i', $physId);
    $stmtGetPhysNormal->execute();
    $stmtGetPhysNormal->bind_result($text);
    $stmtGetPhysNormal->fetch();
    $stmtGetPhysNormal->close();

    $errorMsg = "";
    $success = true;
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
} else {
    $errorMsg = "Failed to prepare SQL statement";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
    exit;
}
?>