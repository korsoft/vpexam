<?php
include_once 'db_connect.php';
include_once 'functions.php';

sec_session_start(); // Our custom secure way of starting a PHP session.

if (!isset($_POST['username'], $_POST['pwd'])) {
    echo(json_encode(array("authed" => false)));
    exit();
}


?>