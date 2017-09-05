<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

parse_str($_SERVER['QUERY_STRING']);

$success = false;
$errorMsg = "";
$text = "";

if (!isset($patientId) || !isset($examId) || !isset($physId)) {
	$errorMsg = "Invalid parameters";
	echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
	exit;
}

// Clipboard data is stored on disk, not in the DB, due to the unknown size. Size could potentially exceed the maximum
// length allowed in the database.

$filename = "clipboard.txt";
$path = "/var/www/.uploads/".$physId."/".$patientId."/".$examId."/".$filename;

if (!file_exists($path)) {
	if (!touch($path)) {
		$errorMsg = "Error creating clipboard file";
		echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
		exit;
	}
}

$text = file_get_contents($path);
$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
?>