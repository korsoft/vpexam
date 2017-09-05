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
$zipPwd = $_POST["zip_pwd"];
$examId = $_POST["exam_id"];

// Return values
$success = false;
$errorMsg = "";

$targetDir = "/var/www/.uploads/".$physId."/".$patientId."/".$examId."/audio/";
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
	// Now that the file has been moved, extract the contents
	$zip = new ZipArchive();
	$zipStatus = $zip->open($targetFile);
	
	if ($zipStatus == true) {
		if ($zip->setPassword($zipPwd)) {
			if (!$zip->extractTo($targetDir)) {
				$success = false;
				$errorMsg = "Extraction failed. The password provided may be incorrect: ".$zip->getStatusString();
				echo json_encode(array("success" => $success, "error" => $errorMsg));
			} else {
				$success = true;
				$errorMsg = "";
				echo json_encode(array("success" => $success, "error" => $errorMsg));
			}
		} else {
			$success = false;
			$errorMsg = "Error setting zip password.";
			echo json_encode(array("success" => $success, "error" => $errorMsg));
		}
		$zip->close();
	} else {
		$success = false;
		$errorMsg = "Failed opening archive: " . @$zip->getStatusString() . " (code: " . $zipStatus . ")";
		echo json_encode(array("success" => $success, "error" => $errorMsg));
	}
	unlink($targetFile);
} else {
	$success = false;
	$errorMsg = "Error moving uploaded file from temp directory to target location.";
	echo json_encode(array("success" => $success, "error" => $errorMsg));
}
?>
