<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";
$token = "";

if (!isset($_GET["id"])) {
    $success = false;
    $error = "ID not specified as query string parameter.";
    $token = "";
    echo(json_encode(array("success" => $success, "errorMsg" => $error, "token" => $token)));
    exit();
}

$id = $_GET["id"];
$userCount = 0;
$prepStmtCheckUserExists = "SELECT COUNT(patient_id) FROM patients WHERE patient_id = ?";
$stmtCheckUserExists = $mysqli->prepare($prepStmtCheckUserExists);
if ($stmtCheckUserExists) {
    $stmtCheckUserExists->bind_param('i', $id);
    $stmtCheckUserExists->execute();
    $stmtCheckUserExists->bind_result($userCount);
    $stmtCheckUserExists->fetch();
    $stmtCheckUserExists->close();
} else {
    $success = false;
    $error = "Error preparing MySQL statement.";
    $token = "";
    echo(json_encode(array("success" => $success, "errorMsg" => $error, "token" => $token)));
    exit();
}

// Check physicians
if ($userCount === 0) {
    $prepStmtCheckUserExists = "SELECT COUNT(physician_id) FROM physicians WHERE physician_id = ?";
    $stmtCheckUserExists = $mysqli->prepare($prepStmtCheckUserExists);
    if ($stmtCheckUserExists) {
        $stmtCheckUserExists->bind_param('i', $id);
        $stmtCheckUserExists->execute();
        $stmtCheckUserExists->bind_result($userCount);
        $stmtCheckUserExists->fetch();
        $stmtCheckUserExists->close();
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        $token = "";
        echo(json_encode(array("success" => $success, "errorMsg" => $error, "token" => $token)));
        exit();
    }
}

// Generate a unique token, and put it in the DB
if ($userCount > 0) {
    $token = sha1(uniqid((string)$userCount, true));

    $prepStmtInsertToken = "INSERT INTO pending_users (user_id, token, tstamp) VALUES (?, ?, ?)";
    $stmtInsertToken = $mysqli->prepare($prepStmtInsertToken);
    if ($stmtInsertToken) {
        $stmtInsertToken->bind_param("isi", $id, $token, $_SERVER["REQUEST_TIME"]);
        if ($stmtInsertToken->execute()) {
            $stmtInsertToken->close();
            $success = true;
            $error = "";
            echo(json_encode(array("success" => $success, "errorMsg" => $error, "token" => $token)));
            exit();
        }
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        $token = "";
        echo(json_encode(array("success" => $success, "errorMsg" => $error, "token" => $token)));
        exit();
    }
} else {
    $success = false;
    $error = "User ID does not exist in database.";
    $token = "";
    echo(json_encode(array("success" => $success, "errorMsg" => $error, "token" => $token)));
    exit();
}
?>