<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";
$bytesWritten = 0;
$lastModified = 0;

if (!isset($_POST['patientId']) || !isset($_POST['examId']) || !isset($_POST['physId']) || !isset($_POST['content'])) {
	$errorMsg = "Invalid parameters";
	echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
	exit();
}

$patientId = $_POST['patientId'];
$examId = $_POST['examId'];
$physId = $_POST['physId'];
$content = $_POST['content'];

// Clipboard data is stored on disk, not in the DB, due to the unknown size. Size could potentially exceed the maximum
// length allowed in the database.
$filename = "clipboard.txt";
$path = "/var/www/.uploads/".$physId."/".$patientId."/".$examId."/".$filename;

$returnVal = file_put_contents($path, $content);
if ($returnVal === false) {
	$success = false;
	$errorMsg = "Error outputting content to file";
	echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "text" => $text)));
	exit();
}

$lastModified = filemtime($path);

$success = true;
$errorMsg = "";
$bytesWritten = $returnVal;
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "bytesWritten" => $bytesWritten, "lastModified" => $lastModified)));
?>