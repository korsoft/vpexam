<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

$success = false;
$errorMsg = "";

$physId = $_GET['physId'];
$patientId = $_GET['patientId'];
$examId = $_GET['examId'];

if (isset($physId) && isset($patientId) && isset($examId)) {
    // First delete the exam record from the database
    $prepStmtDeleteExam = "DELETE FROM exams WHERE exam_id = ? AND patient_id = ? AND physician_id = ?";
    $stmtDeleteExam = $mysqli->prepare($prepStmtDeleteExam);
    if ($stmtDeleteExam) {
        $stmtDeleteExam->bind_param('iii', $examId, $patientId, $physId);
        $stmtDeleteExam->execute();
        if ($stmtDeleteExam->affected_rows < 1) {
            // Emit a warning that there was no matching row in the database.
            // However, proceed to delete the folder, just in case it's there.
            error_log("MySQL DELETE statement affected 0 rows. physId=" . $physId . ", patientId=" . $patientId . ", examId=" . $examId);
        }
        $stmtDeleteExam->close();

        // Now delete the physical exam files from disk
        $examDir = "/var/www/.uploads/" . $physId . "/" . $patientId . "/" . $examId;
        if (!file_exists($examDir)) {
            $success = false;
            $errorMsg = "Cannot delete exam directory. The directory doesn't exist.";
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
            exit();
        }
        system('/bin/rm -rf ' . escapeshellarg($examDir));
    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
        exit();
    }
} else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}

$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
?>
