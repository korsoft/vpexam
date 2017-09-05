<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../util/debugUtil.php';

sec_session_start();

$success = false;
$errorMsg = "";

if (!isset($_POST['u'], $_POST['pwdHashedOld'], $_POST['pwdHashedNew'], $_POST['userId'])) {
    $success = false;
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
} else {
    $user = $_POST['u'];
    $userId = $_POST['userId'];
    $pwdOld = $_POST['pwdHashedOld'];
    $pwdNew = $_POST['pwdHashedNew'];

    if (($user == "physician") || ($user == "patient")) {
        // First check to see if the old password provided matches the one in the DB
        $result = checkPassword($user, $userId, $pwdOld, $mysqli);
        $result = json_decode($result);

        if ($result->success) {
            if ($result->pwdCorrect) {
                // Change password
                $result = changePassword($user, $userId, $pwdNew, $mysqli);
                echo($result);
                exit();
            } else {
                $success = false;
                $errorMsg = "Password specified does not match the one in the database.";
                echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
                exit();
            }
        } else {
            $success = false;
            $errorMsg = "Error checking database password: " . $result->errorMsg;
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
            exit();
        }
    } else {
        $success = false;
        $errorMsg = "'User' parameter must be set to either 'physician' or 'patient'.";
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
        exit();
    }
}

?>