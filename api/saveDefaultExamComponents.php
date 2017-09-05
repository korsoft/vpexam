<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";

if (!isset($_POST['physId'], $_POST['ecString'])) {
    $success = false;
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
} else {
    $physId = $_POST['physId'];
    $ecStr = $_POST['ecString'];

    $prepStmtInsertECString = "INSERT INTO physician_prefs (id, exam_components)
      VALUES(?, ?) ON DUPLICATE KEY UPDATE
      id = VALUES(id),
      exam_components = VALUES(exam_components)";
    $stmtInsertECString = $mysqli->prepare($prepStmtInsertECString);
    if ($stmtInsertECString) {
        $stmtInsertECString->bind_param('is', $physId, $ecStr);
        $stmtInsertECString->execute();
        $stmtInsertECString->close();
    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL insert statement.";
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
        exit();
    }

    $success = true;
    $errorMsg = "";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
}

?>