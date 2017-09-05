<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
include_once 'functions.php';

if (isset($_GET['physId'])) {
    $haveBAA = false;

    $baaPath = "/var/www/.uploads/" . $_GET['physId'] . "/baa.pdf";

    $needBAA = isBAANeeded($_GET['physId'], $mysqli);
    if (!$needBAA)
        $haveBAA = false;
    else
        $haveBAA = file_exists($baaPath);

    echo(json_encode(array(
        "needBAA" => $needBAA,
        "haveBAA" => $haveBAA
    )));
} else {
    echo(json_encode(array(
        "errorMsg" => "Required physisian ID parameter not set."
    )));
}
?>
