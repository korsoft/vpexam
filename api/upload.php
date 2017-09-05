<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../util/debugUtil.php';
include_once '../includes/Mailer.php';

sec_session_start();

$fileName     = $_FILES['file']['name'];	 // The file name
$fileTmpLoc   = $_FILES['file']['tmp_name']; // File in the PHP tmp folder
$fileType     = $_FILES['file']['type'];	 // The type of file it is
$fileSize     = $_FILES['file']['size'];	 // File size in bytes
$fileErrorMsg = $_FILES['file']['error'];	 // 0 for false... and 1 for true
varErrorLog($_FILES['file']);

// Get POST parameters
$examId        = $_POST['examId'];
$physId        = $_POST['physicianId'];
$patientId     = $_POST['patientId'];
$incline       = $_POST['incline'];
$posture       = $_POST['posture'];
$temp          = $_POST['temp'];
$bloodPressure = $_POST['bloodPressure'];
$pulse         = $_POST['pulse'];
$respRate      = $_POST['respRate'];
$pulseOx       = $_POST['pulseOx'];
$painLevel     = $_POST['painLevel'];
$examParts     = $_POST['examParts'];
$history       = $_POST['history'];
$symptoms      = $_POST['symptoms'];
$debug = true;//(isset($_POST["debug"]) ? $_POST["debug"] : false);

// Return values
$success   = false;
$errorMsg  = '';
$debugVals = [];

$targetDir  = "/var/www/.uploads/$physId/$patientId/$examId/";
$targetFile = $targetDir.basename($fileName);
if ($debug) {
	$debugVals["targetDir"]  = $targetDir;
	$debugVals["targetFile"] = $targetFile;
}
/*
if (!$fileTmpLoc) {
	$success = false;
	$errorMsg = "No File uploaded. Either one was not specified, or an error occurred during upload.";
	$returnVals = [
        "success" => $success,
        "error" => $errorMsg
	];
	if ($debug)
		$returnVals["debug"] = $debugVals;
    varErrorLog($returnVals);
	echo json_encode($returnVals);
	exit();
}
*/
if(45 < $fileSize) {
	if (file_exists($targetDir) == false) {
		// Create the directory structure with mode 0777 (all users)
		if (!(mkdir($targetDir, 0777, true))) {
			$success  = false;
			$errorMsg = "Error creating target directory.";
			$returnVals = [
	            'success' => $success,
	            'error' => $errorMsg
			];
			if($debug)
				$returnVals["debug"] = $debugVals;
	        varErrorLog($returnVals);
			echo json_encode($returnVals);
			exit();
		}
	}
	if (move_uploaded_file($fileTmpLoc, $targetFile)) {
		// Now that the file has been moved, extract the contents
		try {
			// Decompress from gz
			$p = new PharData($targetFile);
			$p->decompress();
			unset($p);

			// Unarchive from the tar
			$tarFilename = substr($targetFile, 0, strlen($targetFile) - 3);
			$phar        = new PharData($tarFilename);
			$phar->extractTo($targetDir);
		}
		catch(Exception $e) {
			$success = false;
			$errorMsg = "Exception occurred while decompressing/extracting archive: {$e->getMessage()}";
			$returnVals = [
				'success' => $success,
				'error' => $errorMsg
			];
	        varErrorLog($returnVals);
			echo json_encode($returnVals);
			exit();
		}
		unlink($tarFilename);
		unlink($targetFile);
	} else {
		$success = false;
		$errorMsg = 'Error moving uploaded file from temp directory to target location.';
		$returnVals = [
				'success' => $success,
				'error' => $errorMsg
		];
		if ($debug)
			$returnVals['debug'] = $debugVals;
	    varErrorLog($returnVals);
		echo json_encode($returnVals);
	    exit();
	}
}
else {
	error_log("API::UPLOAD::Notice: No video or audio uploaded for physician { $physId }, patient { $patientId }.");
}
// Now that we've got the zip file extracted, we need to store
// the POST parameters in the DB
// First, check to see if a patient<->physician link exists
$linkId = -1;
$prepStmtCheckLink = 'SELECT id FROM patient_physicians WHERE id = ? AND physician_id = ?';
$stmtCheckLink = $mysqli->prepare($prepStmtCheckLink);
if ($stmtCheckLink) {
	$stmtCheckLink->bind_param('ii', $patientId, $physId);
	$stmtCheckLink->execute();
	$stmtCheckLink->bind_result($linkId);
	$stmtCheckLink->fetch();
	if ($debug) {
		$debugVals["checkLink"]["errNo"] = $stmtCheckLink->errno;
		$debugVals["checkLink"]["errorStr"] = $stmtCheckLink->error;
		$debugVals["checkLink"]["sqlState"] = $stmtCheckLink->sqlstate;
	}
	if ($stmtCheckLink->num_rows < 1) {
		// A link between this patient and physician does not exist in the DB,
		// therefore we must create one
		$stmtCheckLink->close();
		$prepStmtCreateLink = "INSERT IGNORE INTO patient_physicians VALUES (?, ?)";
		$stmtCreateLink = $mysqli->prepare($prepStmtCreateLink);
		if ($stmtCreateLink) {
			$stmtCreateLink->bind_param('ii', $patientId, $physId);
            $stmtCreateLink->execute();
			if ($debug) {
				$debugVals["createLink"]["errNo"] = $stmtCreateLink->errno;
				$debugVals["createLink"]["errorStr"] = $stmtCreateLink->error;
				$debugVals["createLink"]["sqlState"] = $stmtCreateLink->sqlstate;
			}
            varErrorLog($debugVals);
            $stmtCreateLink->close();
		} else {
            $success = false;
            $errorMsg = "Error preparing 'create link' statement.";
			$returnVals = [
					"success" => $success,
					"error" => $errorMsg
			];
			if ($debug)
				$returnVals["debug"] = $debugVals;
            varErrorLog($returnVals);
			echo json_encode($returnVals);
            exit();
		}
	} else {
		// No need to create a new link
        varErrorLog($debugVals);
		$stmtCheckLink->close();
	}
} else {
    $success = false;
    $errorMsg = "Error preparing 'check link' statement.";
	$returnVals = [
			"success" => $success,
			"error" => $errorMsg
	];
	if ($debug)
		$returnVals["debug"] = $debugVals;
    varErrorLog($returnVals);
	echo json_encode($returnVals);
}

// Second, we need to insert the exam POST data into the database
$prepStmtInsertExamData = "INSERT INTO exams (exam_id, exam_parts, patient_id, physician_id, incline, posture, temperature, temp_unit, bp_systolic, bp_diastolic, pulse, resp_rate, pulse_oximetry, pain_level, symptoms) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmtInsertExamData = $mysqli->prepare($prepStmtInsertExamData);
if ($stmtInsertExamData) {
    // Blood pressure is a JSON object containing systolic and diastolic parts
    // so we must decode the JSON
    $bp = json_decode($bloodPressure);
	$temp = json_decode($temp);
    $stmtInsertExamData->bind_param('isiiisdsiiiiiss', $examId, $examParts, $patientId, $physId, $incline, $posture, $temp->tempValue, $temp->unit, $bp->systolic, $bp->diastolic, $pulse, $respRate, $pulseOx, $painLevel, $symptoms);
    $stmtInsertExamData->execute();
	if ($debug) {
		$debugVals["insertExamData"]["errNo"] = $stmtInsertExamData->errno;
		$debugVals["insertExamData"]["errorStr"] = $stmtInsertExamData->error;
		$debugVals["insertExamData"]["sqlState"] = $stmtInsertExamData->sqlstate;
	}
    varErrorLog($debugVals);
    $stmtInsertExamData->close();
} else {
    $success = false;
    $errorMsg = "Error preparing 'insert exam data' statement.";
	$returnVals = [
			"success" => $success,
			"error" => $errorMsg
	];
	if ($debug)
		$returnVals["debug"] = $debugVals;
    varErrorLog($returnVals);
	echo json_encode($returnVals);
    exit();
}

// Third we need to insert the history POST data into the database
$histArr = json_decode($history, true);

$prepStmtInsertHistData = "INSERT INTO patient_history VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmtInsertHistData = $mysqli->prepare($prepStmtInsertHistData);
if ($stmtInsertHistData) {
    $stmtInsertHistData->bind_param(
        'iissssssssssssssssss',
        $patientId,
        $examId,
        $histArr['main_cond'],
        $histArr['symptom_loc'],
        $histArr['symptom_quality'],
        $histArr['symptom_severity'],
        $histArr['symptom_length'],
        $histArr['symptom_timing'],
        $histArr['symptom_better'],
        $histArr['symptom_worse'],
        $histArr['symptom_problems'],
        $histArr['past_med_cond'],
        $histArr['past_surgeries'],
        $histArr['family_hist_med'],
        $histArr['do_you_smoke'],
        $histArr['smoke_packs'],
        $histArr['alc_consumption'],
        $histArr['illicit_drugs'],
        $histArr['allergies'],
        $histArr['current_meds']
    );

    $stmtInsertHistData->execute();

	if ($debug) {
		$debugVals["insertHistData"]["errNo"] = $stmtInsertHistData->errno;
		$debugVals["insertHistData"]["errorStr"] = $stmtInsertHistData->error;
		$debugVals["insertHistData"]["sqlState"] = $stmtInsertHistData->sqlstate;
	}
    varErrorLog($debugVals);
	$stmtInsertHistData->close();
} else {
	$errorMsg = "Error preparing 'insert history data' statement.";
	$returnVals = [
		"success" => $success,
		"error" => $errorMsg
	];
	if ($debug)
		$returnVals["debug"] = $debugVals;
    varErrorLog($returnVals);
	echo json_encode($returnVals);
	exit();
}

$errorMsg = "";
$success = true;
$returnVals = [
		"success" => $success,
		"error" => $errorMsg
];
if ($debug)
	$returnVals["debug"] = $debugVals;
varErrorLog($returnVals);
//Send notification email to physician
try {
	$infoforemail = getInfoForEmail($mysqli, $patientId, $physId);
    $mailer = new Mailer('exam_uploaded', '{{sender}} has uploaded a VPExam', $infoforemail[0][0], $infoforemail[1], [
        'url'        => 'https://vpexam.com/',
        'img_header' => 'https://vpexam.com/img/logo_img.png'
    ]);
    $mailer->send();
}
catch(Exception $e) {
    error_log(__METHOD__ . $e->getMessage());
}

echo json_encode($returnVals);
