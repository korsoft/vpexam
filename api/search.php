<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../includes/psl-config.php';

sec_session_start();

parse_str($_SERVER['QUERY_STRING']);

// Return values
$success = false;
$errorMsg = "";
$results = [];
$allPatients = [];

if (!isset($physId) || ($fname == '' && $lname == '')) {
	$success = false;
	$errorMsg = "Invalid parameters";
	$results = [];
	echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
} else {
	$haveFirstName = !empty($fname);
	$haveLastName = !empty($lname);
	$haveMale = ($gender == "m");
	$haveFemale = ($gender == "f");
	$haveGender = ($haveMale || $haveFemale);
	$prepStmt = "SELECT patient_id, username, first_name, last_name, email, mrn, gender, dob, phone, address, city, state, zip FROM patients WHERE ";
	// How can you not love the conditional statement?
	$prepStmt .= ($haveFirstName ? "first_name RLIKE ? " : "");
	$prepStmt .= (($haveFirstName && $haveLastName) ? "AND " : "");
	$prepStmt .= ($haveLastName ? "last_name RLIKE ? " : "");
	$prepStmt .= (($haveGender ? ($haveMale ? "AND gender = 'male'" : ($haveFemale ? "AND gender = 'female'" : "")) : ""));
	$stmt = $mysqli->prepare($prepStmt);
	if ($stmt) {
		$id = -1;
		$username = "";
		$firstName = "";
		$lastName = "";
		$email = "";
		$mrn = "";
		$gender = "";
		$dob = "";
		$phone = -1;
		$address = "";
		$city = "";
		$state = "";
		$zip = -1;
		if ($haveFirstName && $haveLastName)
			$stmt->bind_param('ss', $fname, $lname);
		else if ($haveFirstName)
			$stmt->bind_param('s', $fname);
		else if ($haveLastName)
			$stmt->bind_param('s', $lname);
		$stmt->execute();
		$stmt->bind_result($id, $username, $firstName, $lastName, $email, $mrn, $gender, $dob, $phone, $address, $city, $state, $zip);
		while ($stmt->fetch()) {
			$patient = array("patientId" => $id, "username" => $username, "firstName" => $firstName, "lastName" => $lastName, "email" => $email, "mrn" => $mrn, "gender" => $gender, "dob" => $dob, "phone" => $phone, "address" => $address, "city" => $city, "state" => $state, "zip" => $zip);
			array_push($allPatients, $patient);
		}
		$stmt->close();
		// Now we've got all the patients matching the search query, but we have to remove the ones that are not that physician's patients
	} else {
		$success = false;
		$errorMsg = "Error preparing search SQL statement.";
		$results = [];
		echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
		exit();
	}
	$prepStmt = "SELECT id FROM patient_physicians WHERE physician_id = ?";
	$stmt = $mysqli->prepare($prepStmt);
	if ($stmt) {
		$physiciansPatients = [];
		$patient = -1;
		$stmt->bind_param('i', $physId);
		$stmt->execute();
		$stmt->bind_result($patient);
		while ($stmt->fetch()) {
			array_push($physiciansPatients, $patient);
		}

		// Now remove all of the patients that don't belong to this physician
		$num = count($physiciansPatients);
		$foundElem = false;
		foreach ($allPatients as $arr) {
			for ($i = 0; $i < $num; $i++) {
				if ($arr['patientId'] == $physiciansPatients[$i]) {
					array_push($results, $arr);
				}
			}
		}
		$success = true;
		$errorMsg = "";
		echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
	} else {
		$success = false;
		$errorMsg = "Error preparing patient fetch SQL statement.";
		$results = [];
		echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
	}
}
?>