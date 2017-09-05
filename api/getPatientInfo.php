<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

if (isset($_GET['patientId'])) {
    $patientInfo = [];
    $prepStmtGetPatientInfo = "SELECT patient_id, first_name, middle_name, last_name, gender, dob, phone FROM patients WHERE patient_id = ?";
    $stmtGetPatientInfo = $mysqli->prepare($prepStmtGetPatientInfo);
    if ($stmtGetPatientInfo) {
        $id = -1;
        $firstName = "";
        $middleName = "";
        $lastName = "";
        $gender = "";
        $dob = "";
        $phone = "";
        $stmtGetPatientInfo->bind_param('i', $_GET['patientId']);
        $stmtGetPatientInfo->execute();
        $stmtGetPatientInfo->bind_result($id, $firstName, $middleName, $lastName, $gender, $dob, $phone);
        $stmtGetPatientInfo->fetch();
        $stmtGetPatientInfo->close();
        $patientInfo['id'] = $id;
        $patientInfo['firstName'] = $firstName;
        $patientInfo['middleName'] = $middleName;
        $patientInfo['lastName'] = $lastName;
        $patientInfo['gender'] = $gender;
        $patientInfo['dob'] = $dob;
        $patientInfo['phone'] = $phone;
    }
    $assocPhys = [];
    $prepStmtGetAssocPhys = "SELECT physician_id FROM patient_physicians WHERE id = ?";
    $stmtGetAssocPhys = $mysqli->prepare($prepStmtGetAssocPhys);
    if ($stmtGetAssocPhys) {
        $physId = -1;
        $stmtGetAssocPhys->bind_param('i', $_GET['patientId']);
        $stmtGetAssocPhys->execute();
        $stmtGetAssocPhys->bind_result($physId);
        while ($stmtGetAssocPhys->fetch())
            $assocPhys[] = $physId;
        $stmtGetAssocPhys->close();
    }
    $patientInfo['associatedPhys'] = $assocPhys;

    $array = array(
        "patientInfo" => $patientInfo,
        "error" => "",
        "success" => true
    );
    echo(json_encode($array));
    exit();
} else {
	$patientInfo = [];
	$array = array(
		"patientInfo" => $patientInfo,
		"error" => "Invalid parameters.",
        "success" => false
	);
	echo(json_encode($array));
    exit();
}
?>
