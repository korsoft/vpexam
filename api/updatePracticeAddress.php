<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$error = "";
$success = false;
if (!isset($_POST['id']) || !isset($_POST['addr']) || !isset($_POST['city']) || !isset($_POST['state']) || !isset($_POST['zip'])) {
    $success = false;
    $error = "Missing one or more of the required parameters.";
    echo(json_encode(array("error" => $error, "success" => $success)));
    exit();
}

$id = $_POST['id'];
$name = $_POST['name'];
$addr = $_POST['addr'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$prepStmtUpdatePracticeAddress = "UPDATE physicians SET practice_name = ?, practice_addr = ?, practice_city = ?, practice_state = ?, practice_zip = ? WHERE physician_id = ?";
$stmtUpdatePracticeAddress = $mysqli->prepare($prepStmtUpdatePracticeAddress);
if ($stmtUpdatePracticeAddress) {
    $stmtUpdatePracticeAddress->bind_param('sssssi', $name, $addr, $city, $state, $zip, $id);
    $stmtUpdatePracticeAddress->execute();
    $stmtUpdatePracticeAddress->close();

    $error = "";
    $success = true;
    echo(json_encode(array("error" => $error, "success" => $success)));
    exit();
} else {
    var_dump($mysqli);
    var_dump($stmtUpdatePracticeAddress);
    $success = false;
    $error = "Error preparing mysql statement.";
    echo(json_encode(array("error" => $error, "success" => $success)));
    exit();
}
?>