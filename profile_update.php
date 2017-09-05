<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();

$exPatientInfo = new ExtendedPatientInfo($_SESSION['username'], $_SESSION['user_id'], $_SESSION['first_name'], $_SESSION['last_name'], $_POST['email'], $_POST['mrn'], $_POST['gender'], $_POST['phone'], $_POST['dob'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['zip']);

updateExtendedPatientInfo($exPatientInfo, $mysqli);

header("Location: profile_patient.php");
?>
