<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";
$recentList = "";

if (isset($_GET['physId']) && isset($_GET['patientId'])) {
    $physId = $_GET['physId'];
    $patientId = $_GET['patientId'];
    $prepStmtGetRecentList = "SELECT recently_viewed FROM physicians WHERE physician_id = ?";
    $stmtGetRecentList = $mysqli->prepare($prepStmtGetRecentList);
    if ($stmtGetRecentList) {
        $stmtGetRecentList->bind_param('i', $physId);
        $stmtGetRecentList->execute();
        $stmtGetRecentList->bind_result($recentList);
        $stmtGetRecentList->fetch();
        $stmtGetRecentList->close();
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "error" => $error)));
        exit();
    }
    $recentList = json_decode($recentList);

    if (is_array($recentList) && count($recentList) > 0) {
        $idx = array_search($patientId, $recentList);

        if (!($idx === false)) {
            unset($recentList[$idx]);
            $recentList = array_filter($recentList, 'strlen');
            $recentList = array_values($recentList);
        } else {
            $success = false;
            $error = "Item not found in array.";
            echo(json_encode(array("success" => $success, "error" => $error)));
            exit();
        }
    } else {
        $success = false;
        $error = "Null or empty array; no item to remove.";
        echo(json_encode(array("success" => $success, "error" => $error)));
        exit();
    }

    $recentList = json_encode($recentList);
    // Now, add the new list to the DB
    $prepStmtUpdateRecentList = "UPDATE physicians SET recently_viewed = ? WHERE physician_id = ?";
    $stmtUpdateRecentList = $mysqli->prepare($prepStmtUpdateRecentList);
    if ($stmtUpdateRecentList) {
        $stmtUpdateRecentList->bind_param('si', $recentList, $physId);
        $stmtUpdateRecentList->execute();
        $stmtUpdateRecentList->close();
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "error" => $error)));
        exit();
    }

    $success = true;
    $error = "";
    echo(json_encode(array("success" => $success, "error" => $error)));
} else {
    $success = false;
    $error = "Invalid or missing parameters.";
    echo(json_encode(array("success" => $success, "error" => $error)));
}
?>