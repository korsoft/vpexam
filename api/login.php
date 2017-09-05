<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
 
sec_session_start();

function loginAPI($email, $password, $mysqli) {
	// Using prepared statements means that SQL injection is not possible.
    $prep_stmt_physician = "SELECT physician_id, username, password, salt, first_name, last_name FROM physicians WHERE email = ? LIMIT 1";
    $prep_stmt_patient = "SELECT patient_id, mrn, username, password, salt, first_name, last_name, gender FROM patients WHERE email = ? LIMIT 1";
    $stmtPhys = $mysqli->prepare($prep_stmt_physician);
    $stmtPatient = $mysqli->prepare($prep_stmt_patient);
	$user_id = -1;
	$mrn = "";
	$username = "";
	$db_password = "";
	$salt = "";
	$first_name = "";
	$last_name = "";
	$gender = "";
	$isPatient = true;
	$patientIds = [];
	$physicianIds = [];
	$patientInfos = [];
	$physicianInfos = [];
	if ($stmtPatient && $stmtPhys) {
        $stmtPatient->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmtPatient->execute();    // Execute the prepared query.
        $stmtPatient->store_result();
 
        // get variables from result.
        $stmtPatient->bind_result($user_id, $mrn, $username, $db_password, $salt, $first_name, $last_name, $gender);
        $stmtPatient->fetch();
			
		if ($stmtPatient->num_rows < 1) {
			$stmtPhys->bind_param('s', $email);
			$stmtPhys->execute();
			$stmtPhys->store_result();
			
			$stmtPhys->bind_result($user_id, $username, $db_password, $salt, $first_name, $last_name);
			$stmtPhys->fetch();

			if ($stmtPhys->num_rows < 1) {
				// User does not exist
				return NULL;
			} else if ($stmtPhys->num_rows > 1) {
				// Should never happen
				return NULL;
			}
			$isPatient = false;
		} else if ($stmtPatient->num_rows > 1) {
			// Should never happen.
			return NULL;
		}
	}
	
	// hash the password with the unique salt.
	$password = hash('sha512', $password . $salt);

	// Check if the password in the database matches
	// the password the user submitted.
	if ($db_password == $password) {
		// Password is correct!
		// XSS protection as we might print this value
		$user_id = preg_replace("/[^0-9]+/", "", $user_id);
		// XSS protection as we might print this value
		$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
		if ($isPatient) {
			$singleId = -1;
			$prepStmtGetPatientPhys = "SELECT physician_id FROM patient_physicians WHERE id = ?";
			$stmtGetPatientPhys = $mysqli->prepare($prepStmtGetPatientPhys);
			if ($stmtGetPatientPhys) {
				$stmtGetPatientPhys->bind_param('i', $user_id);
				$stmtGetPatientPhys->execute();
				$stmtGetPatientPhys->bind_result($singleId);
				while ($stmtGetPatientPhys->fetch()) {
					array_push($physicianIds, $singleId);
				}
				$stmtGetPatientPhys->close();

				// Now, get a PhysicianInfo object for each physician ID
				$physNum = count($physicianIds);
				for ($i = 0; $i < $physNum; $i++) {
					array_push($physicianInfos, getPhysicianInfo($physicianIds[$i], $mysqli));
				}
			}
		} else {
			$singleId = -1;
			$prepStmtGetPhysPatients = "SELECT id FROM patient_physicians WHERE physician_id = ?";
			$stmtGetPhysPatients = $mysqli->prepare($prepStmtGetPhysPatients);
			if ($stmtGetPhysPatients) {
				$stmtGetPhysPatients->bind_param('i', $user_id);
				$stmtGetPhysPatients->execute();
				$stmtGetPhysPatients->bind_result($singleId);
				while ($stmtGetPhysPatients->fetch()) {
					array_push($patientIds, $singleId);
				}
				$stmtGetPhysPatients->close();

				// Now, get a PatientInfo object for each patient ID
				$patientNum = count($patientIds);
				for ($i = 0; $i < $patientNum; $i++) {
					array_push($patientInfos, getPatientInfo(/*$user_id,*/$patientIds[$i], $mysqli));
				}
			}
		}
		// Login successful.
		$array = array(
			"authed" => true,
			"userId" => $user_id,
			"mrn" => $mrn,
			"username" => $username,
			"firstName" => $first_name,
			"lastName" => $last_name,
			"gender" => $gender,
			"isPatient" => $isPatient,
			"patientIds" => $patientIds,
			"physicianIds" => $physicianIds,
			"patientInfos" => $patientInfos,
			"physicianInfos" => $physicianInfos
		);
		return $array;
	} else {
		return NULL;
	}

	// If the user exists we check if the account is locked
	// from too many login attempts
}

if (isset($_POST['username'], $_POST['password'])) {
	$username = $_POST['username'];
	$pwd = $_POST['password']; // Hashed password
	
	$loginCheck = loginAPI($username, $pwd, $mysqli);

	if ($loginCheck['authed'] == true) {
		echo json_encode($loginCheck);
	} else {
		$authed = false;
		$errorMsg = "Invalid username or password";
		$array = array(
			"authed" => $authed,
			"errorMsg" => $errorMsg,
			"username" => $loginCheck['username'],
			"userId" => $loginCheck['userId']
		);
		echo json_encode($array);
	}
} else {
	$errorMsg = "Invalid Parameters";
	$authed = false;
	$array = array(
		"authed" => $authed,
		"errorMsg" => $errorMsg,
		"username" => "",
		"userId" => -1
	);
	echo json_encode($array);
}
?>

