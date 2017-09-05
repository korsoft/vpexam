<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
include_once 'functions.php';

if (isset($_GET['physId'], $_GET['needBAA'])) {
    if ($_GET['needBAA'] == 'true')
        setBAANeeded($_GET['physId'], true, $mysqli);
    else if ($_GET['needBAA'] == 'false')
        setBAANeeded($_GET['physId'], false, $mysqli);

    echo(json_encode(array(
        "success" => true,
        "errorMsg" => ""
    )));
} else {
    echo(json_encode(array(
        "success" => false,
        "errorMsg" => "One or more required parameters was not set."
    )));
}
?>