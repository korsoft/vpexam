<?php
include_once 'includes/functions.php';

sec_session_start();

session_unset();
session_destroy();

header('location:main.php');
exit();