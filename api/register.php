<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
 
sec_session_start();

const BASE_PATH_PATIENTS = '/var/www/.uploads/profile/patients/img/';

error_log('API :: REGISTER :: POST { ' . print_r($_POST, true) . ' }');

function registerAPI($fname, $lname, $dob, $gender, $mysqli) {
	// Using prepared statements means that SQL injection is not possible.
	// Email is our unique identifier, so before attempting an insert into the DB, 
	// we must first ensure that the email does not already exist.
	if (haveEmail()) {
		$email = $_POST['email'];
		$prepStmtCheckExists = "SELECT patient_id FROM patients WHERE email = ?";
		$stmtCheckExists = $mysqli->prepare($prepStmtCheckExists);
		$temp = "";
		if ($stmtCheckExists) {
			$stmtCheckExists->bind_param('s', $email);
			$stmtCheckExists->execute();
			$stmtCheckExists->store_result();

			//$stmtCheckExists->bind_result($temp);
			//$stmtCheckExists->fetch();

			if ($stmtCheckExists->num_rows > 0) {
				// The patient with the specified email already exists in the DB
				$array = array(
						"registered" => false,
						"errorMsg" => "User with the specified email already exists in the database. Line " . __LINE__,
						"userId" => -1
				);
				$stmtCheckExists->close();
				return $array;
			}
		} else {
			$array = array(
					"registered" => false,
					"errorMsg" => "Error preparing SQL statement. Line " . __LINE__,
					"userId" => -1
			);
			return $array;
		}
	}

    // We only need to generate the salt, and hash the password if the user provided a password
    if (havePassword()) {
        $arrPass = hashPassword(hash('sha512', $_POST['pwd']));
    }
	
	// Generate a random number 8-digit number between: 10^7 and (10^8) - 1, or 10000000-99999999.
	// With an 8-digit number, there will be 89,999,999 unique possibilities
	// Also, by using an 8-digit number, we can ensure that it fits into a standard INT column in MySql.
	// This means there is a 1/89999999 chance of getting the same number
	$userId = 0;
    /*
    $digits = 8;
	$num = 0;
	// Force while statement to execute once
	$userCount = 1;
	while ($userCount > 0) {
		$num = mt_rand(pow(10, $digits - 1), pow(10, $digits) - 1);
		if ($stmt = $mysqli->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = '$num'")) {
			$stmt->execute();
			$stmt->bind_result($userCount);
			$stmt->fetch();
			$stmt->close();
		}
	}
    $userId = $num;
    */

    if (isset($_POST['email']))
	    $username = explode('@', $_POST['email'])[0];

	$valuesToInsert = [];
	//$valuesToInsert['patient_id'] = $userId;
	$valuesToInsert['first_name'] = $fname;
	if (isset($_POST['mname']))
		$valuesToInsert['middle_name'] = $_POST['mname'];
	$valuesToInsert['last_name'] = $lname;
    if (isset($username))
        $valuesToInsert['username'] = $username;
    if (isset($_POST['email']))
        $valuesToInsert['email'] = $_POST['email'];
    if (havePassword()) {
        $valuesToInsert['password'] = $arrPass['pwd'];
        $valuesToInsert['salt'] = $arrPass['randomSalt'];
    }
    if (isset($_POST['mrn']))
        $valuesToInsert['mrn'] = $_POST['mrn'];
    $valuesToInsert['gender'] = $gender;
    $valuesToInsert['dob'] = $dob;
    if (isset($_POST['phone']))
        $valuesToInsert['phone'] = $_POST['phone'];
    if (isset($_POST['address']))
        $valuesToInsert['address'] = $_POST['address'];
    if (isset($_POST['city']))
        $valuesToInsert['city'] = $_POST['city'];
    if (isset($_POST['state']))
        $valuesToInsert['state'] = $_POST['state'];
    if (isset($_POST['zip']))
        $valuesToInsert['zip'] = $_POST['zip'];

    /*
    $prepStmtRegister = "INSERT INTO patients(";
    $i = 0;
    $numVals = count($valuesToInsert);
    $lastIndex = $numVals - 1;
    foreach ($valuesToInsert as $key => $val) {
        if ($i < $lastIndex)
            $prepStmtRegister .= ($key . ', ');
        else
            $prepStmtRegister .= ($key . ')');
        $i++;
    }
    $prepStmtRegister .= ' VALUES(';
    for ($i = 0; $i < $numVals; $i++) {
        if ($i < $lastIndex)
            $prepStmtRegister .= '?, ';
        else
            $prepStmtRegister .= '?)';
    }

    $paramTypesStr = 'i';
    $paramTypesStr .= str_repeat('s', $numVals - 1);
    */

    $prepStmtRegister = 'INSERT INTO patients(' . implode(',', array_keys($valuesToInsert)) . ') VALUES("' . implode('","', array_values($valuesToInsert)) .  '")';

    /*if (haveMRN() && havePassword()) {
        $prepStmtRegister = "INSERT INTO patients(patient_id, username, email, password, salt, first_name, middle_name, last_name, mrn, gender, phone, dob, address, city, state, zip) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    } else if (haveMRN() && !havePassword()) {
        $prepStmtRegister = "INSERT INTO patients(patient_id, username, email, first_name, middle_name, last_name, mrn, gender, phone, dob, address, city, state, zip) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    } else if (havePassword() && !haveMRN()) {
        $prepStmtRegister = "INSERT INTO patients(patient_id, username, email, password, salt, first_name, middle_name, last_name, gender, phone, dob, address, city, state, zip) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    } else {
        $prepStmtRegister = "INSERT INTO patients(patient_id, username, email, first_name, middle_name, last_name, gender, phone, dob, address, city, state, zip) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }*/

	$stmtRegister = $mysqli->prepare($prepStmtRegister);
	if ($stmtRegister) {
        /*
        $funcParams = array_values($valuesToInsert);
        array_unshift($funcParams, $paramTypesStr);
        call_user_func_array(array($stmtRegister, "bind_param"), refValues($funcParams));
        */
        /*if (haveMRN() && havePassword())
		    $stmtRegister->bind_param('isssssssssssssss', $userId, $username, $email, $pwd, $randomSalt, $fname, $mname, $lname, $_POST['mrn'], $gender, $phone, $dob, $addr, $city, $state, $zip);
        else if (haveMRN() && !havePassword())
            $stmtRegister->bind_param('isssssssssssss', $userId, $username, $email, $fname, $mname, $lname, $_POST['mrn'], $gender, $phone, $dob, $addr, $city, $state, $zip);
        else if (havePassword() && !haveMRN())
            $stmtRegister->bind_param('issssssssssssss', $userId, $username, $email, $pwd, $randomSalt, $fname, $mname, $lname, $gender, $phone, $dob, $addr, $city, $state, $zip);
        else
            $stmtRegister->bind_param('issssssssssss', $userId, $username, $email, $fname, $mname, $lname, $gender, $phone, $dob, $addr, $city, $state, $zip);*/
		if (!$stmtRegister->execute()) {
			$array = array(
				"registered" => false,
				"errorMsg" => "Error while executing prepared SQL statement Line " . __LINE__,
				"userId" => -1
			);
			$stmtRegister->close();
			return $array;
		}
        $userId = $mysqli->insert_id;
		$stmtRegister->close();

        // Now, create the link between patient and physician
        if (isset($_POST['physId']) && intval($_POST['physId']) > 0) {
            $physId = $_POST['physId'];
            $prepStmtCreateLink = "INSERT INTO patient_physicians(id, physician_id) VALUES(?, ?)";
            $stmtCreateLink = $mysqli->prepare($prepStmtCreateLink);
            if ($stmtCreateLink) {
                $stmtCreateLink->bind_param('ii', $userId, $physId);
                if (!$stmtCreateLink->execute()) {
                    $array = array(
                        "registered" => false,
                        "errorMsg" => "Error while executing prepared SQL statement Line " . __LINE__,
                        "userId" => -1
                    );
                    $stmtCreateLink->close();
                    return $array;
                }
            } else {
                $array = array(
                    "registered" => false,
                    "errorMsg" => "Error while preparing SQL statement Line " . __LINE__,
                    "userId" => -1
                );
                return $array;
            }
        }

        // Now, check to see if a profile picture has been uploaded
        if (count($_FILES) > 0) {
            error_log("Profile picture uploaded. FILES parameter set.");
            // We have a file upload. Retrieve it and move it to the proper directory.
            if (isset($_FILES['profilePic'])) {
                $tmpLocation = $_FILES['profilePic']['tmp_name'];
                $destLocation = BASE_PATH_PATIENTS . $userId . '.png';
                error_log("Temp file location: " . $tmpLocation);
                error_log("Dest file location: " . $destLocation);
                move_uploaded_file($tmpLocation, $destLocation);
            }
        } else {
			error_log("No profile picture uploaded. FILES parameter not set.");
		}

		$array = array(
			"registered" => true,
			"errorMsg" => "",
			"userId" => $userId
		);
		return $array;
	} else {
		$array = array(
			"registered" => false,
			"errorMsg" => "Error while preparing SQL statement Line " . __LINE__,
			"userId" => -1
		);
		return $array;
	}
}

/*function refValues($arr) {
    $refs = array();

    foreach ($arr as $key => $value)
        $refs[$key] = &$arr[$key];

    return $refs;
}*/

function haveMRN() {
    return isset($_POST['mrn']);
}

function havePassword() {
    return isset($_POST['pwd']);
}

function haveEmail() {
	return isset($_POST['email']);
}

if (isset($_POST['fname'], $_POST['lname'], $_POST['dob'], $_POST['gender'])) {
	$arr = registerAPI($_POST['fname'], $_POST['lname'], $_POST['dob'], $_POST['gender'], $mysqli);
	echo json_encode($arr);
} else {
	$errorMsg = "One or more of the required parameters were not set in the POST data.";
	$registered = false;
	$array = array(
		"registered" => $registered,
		"errorMsg" => $errorMsg,
		"userId" => -1
	);
	echo json_encode($array);
}
?>
