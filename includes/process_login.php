<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'debug.php';

if ($DEBUG_ENABLED)
    PHP_Timer::start();

sec_session_start(); // Our custom secure way of starting a PHP session.

if ($DEBUG_ENABLED) {
    $time = PHP_Timer::stop();
    logInfo("'sec_session_start' took " . $time . " ms");
}

if (isset($_POST['username'], $_POST['p'])) {
    $username = $_POST['username'];
    $password = $_POST['p']; // The hashed password.

    if ($DEBUG_ENABLED)
        PHP_Timer::start();
    $checkAuth = login($username, $password, $mysqli);
    if ($DEBUG_ENABLED) {
        $time = PHP_Timer::stop();
        logInfo("'login()' took " . $time . " ms");
    }

    if ($checkAuth["authed"] && $_SESSION["is_patient"]) {
        header('Location: ../patient_main.php');
    } else if ($checkAuth["authed"] && !$_SESSION["is_patient"]) {
        header('Location: ../physician_main.php');
    } else {
        header('Location: ../error.php?error=' . $checkAuth["errorCode"] . '&l=' . $checkAuth["line"]);
        exit();
    }
} else {
    // The correct POST variables were not sent to this page. 
    header('Location: ../error.php?error=4001&l='.__LINE__);
    exit();
}

