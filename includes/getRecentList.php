<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";
$recentList = "";

if (isset($_GET['physId'])) {
    $physId = $_GET['physId'];

    $prepStmtGetRecentList = "SELECT recently_viewed FROM physicians WHERE physician_id = ?";
    $stmtGetRecentList = $mysqli->prepare($prepStmtGetRecentList);
    if ($stmtGetRecentList) {
        $stmtGetRecentList->bind_param('i', $physId);
        $stmtGetRecentList->execute();
        $stmtGetRecentList->bind_result($recentList);
        if (!$stmtGetRecentList->fetch()) {
            $success = false;
            $error = "Invalid physician ID.";
            echo(json_encode(array("success" => $success, "error" => $error, "data" => $recentList)));
            exit();
        }
        $stmtGetRecentList->close();
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "error" => $error, "data" => $recentList)));
        exit();
    }

    $success = true;
    $error = "";
    echo(json_encode(array("success" => $success, "error" => $error, "data" => $recentList)));
} else {
    $success = false;
    $error = "Invalid or missing parameters.";
    echo(json_encode(array("success" => $success, "error" => $error, "data" => $recentList)));
}
?>