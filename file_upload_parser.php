<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();

$fileName = $_FILES["file1"]["name"];	// The file name
$fileTmpLoc = $_FILES["file1"]["tmp_name"];	// File in the PHP tmp folder
$fileType = $_FILES["file1"]["type"];	// The type of file it is
$fileSize = $_FILES["file1"]["size"];	// File size in bytes
$fileErrorMsg = $_FILES["file1"]["error"];	// 0 for false... and 1 for true
$physId = $_POST["physId"];

$userFolder = $_SESSION['user_id'];
$targetDir = ".uploads/".$physId."/".$userFolder."/";
$targetFile = $targetDir.basename($fileName);
if (!$fileTmpLoc) {
	echo "ERROR: Please browse for a file before clicking the upload button.";
	exit();
}

if (move_uploaded_file($fileTmpLoc, $targetFile)) {
	echo "$fileName upload is complete";
} else {
	echo "Upload failed";
}
?>
