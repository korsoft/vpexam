<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$fileName = $_FILES["file"]["name"];	// The file name
$fileTmpLoc = $_FILES["file"]["tmp_name"];	// File in the PHP tmp folder
$fileType = $_FILES["file"]["type"];	// The type of file it is
$fileSize = $_FILES["file"]["size"];	// File size in bytes
$fileErrorMsg = $_FILES["file"]["error"];	// 0 for false... and 1 for true
$physId = $_POST["physician_id"];
$patientId = $_POST["patient_id"];
$examId = $_POST["exam_id"];

// Return values
$success = false;
$errorMsg = "";

$targetDir = "/var/www/.uploads/".$physId."/".$patientId."/".$examId."/text/history/";
$targetFile = $targetDir.basename($fileName);
if (!$fileTmpLoc) {
	$success = false;
	$errorMsg = "No File uploaded. Either one was not specified, or an error occurred during upload.";
	echo json_encode(array("success" => $success, "error" => $errorMsg));
	exit();
}

if (file_exists($targetDir) == false) {
	// Create the directory structure with mode 0777 (all users)
	if (!(mkdir($targetDir, 0777, true))) {
		$success = false;
		$errorMsg = "Error creating target directory.";
		echo json_encode(array("success" => $success, "error" => $errorMsg));
		exit();
	}
}

if (move_uploaded_file($fileTmpLoc, $targetFile)) {
	$success = true;
	$errorMsg = "";
	echo json_encode(array("success" => $success, "error" => $errorMsg));
} else {
	$success = false;
	$errorMsg = "Error moving uploaded file from temp directory to target location.";
	echo json_encode(array("success" => $success, "error" => $errorMsg));
}
?>
