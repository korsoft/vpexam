<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$error_msg = "";
$isPatient = false;
$physId = -1;

if (isset($_POST['username'], $_POST['email'], $_POST['p'], $_POST['role'])) {
	if (isset($_POST['phys_id']))
		$physId = $_POST['phys_id'];
	// Set the boolean value for if the person trying to register is a patient or a physician
	$isPatient = ($_POST['role'] == 'patient');

	// Sanitize and validate the data passed in
	$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		// Not a valid email
		$error_msg .= '<p class="error">The email address you entered is not valid</p>';
	}

	$password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
	if (strlen($password) != 128) {
		// The hashed pwd should be 128 characters long.
		// If it's not, something really odd has happened
		$error_msg .= '<p class="error">Invalid password configuration.</p>';
	}

	// Username validity and password validity have been checked client side.
	// This should should be adequate as nobody gains any advantage from
	// breaking these rules.
	//
	$prep_stmt = "";

	if ($isPatient)
		$prep_stmt = "SELECT patient_id FROM patients WHERE email = ? limit 1";
	else
		$prep_stmt = "SELECT physician_id FROM physicians WHERE email = ? limit 1";
	$stmt = $mysqli->prepare($prep_stmt);
	
	// Check existing email
	if ($stmt) {
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows == 1) {
			// A user with this email address already exists
			$error_msg .= '<p class="error">A user with this email address already exists.</p>';
			$stmt->close();
		}
	} else {
		$error_msg .= '<p class="error">Database error Line 39</p>';
		$stmt->close();
	}

	// Check existing username
	$prep_stmt = "";
	if ($isPatient)
		$prep_stmt = "SELECT patient_id FROM patients WHERE username = ? LIMIT 1";
	else
		$prep_stmt = "SELECT physician_id FROM physicians WHERE username = ? LIMIT 1";
	$stmt = $mysqli->prepare($prep_stmt);

	if ($stmt) {
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows == 1) {
			// A user with this username already exists
                	$error_msg .= '<p class="error">A user with this username already exists</p>';
                	$stmt->close();
		}
		$stmt->close();
	} else {
		$error_msg .= '<p class="error">Database error line 55</p>';
		$stmt->close();
	}

	// Check physician ID, to make sure it exists, if the selected role was patient
	if ($isPatient) {
		$physId = $_POST['phys_id'];
		$prep_stmt = "SELECT physician_id FROM physicians WHERE physician_id = ?";
		$stmt = $mysqli->prepare($prep_stmt);
		if ($stmt) {
			$stmt->bind_param('i', $physId);
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows == 0) {
				$error_msg .= '<p class="error">Physician ID not found in the database.</p>';
				$stmt->close();
			}
		}
	}
	
	// TODO: 
	// We'll also have to account for the situation where the user doesn't have
	// rights to do registration, by checking what type of user is attempting to
	// perform the operation.
	
	if (empty($error_msg)) {
		// Create a random SALT
		// $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE)); // Did not work
		$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
		
		// Create salted password
		$password = hash('sha512', $password . $random_salt);

		$firstName = $_POST['fname'];
		$lastName = $_POST['lname'];
		
		// Generate a random number 8-digit number between: 10^7 and (10^8) - 1, or 10000000-99999999.
		// With an 8-digit number, there will be 89,999,999 unique possibilities
		// Also, by using an 8-digit number, we can ensure that it fits into a standard INT column in MySql.
		// This means there is a 1/89999999 chance of getting the same number
		$digits = 8;
		$num = 0;
		$userId = 0;
		// Force while statement to execute once
		$userCount = 1;
		
		while ($userCount > 0) {
			$num = mt_rand(pow(10, $digits - 1), pow(10, $digits) - 1);
			echo($num);
			if ($stmt = $mysqli->prepare($isPatient ? "SELECT COUNT(*) FROM patients WHERE patient_id = '$num'" : "SELECT COUNT(*) FROM physicians WHERE physician_id = '$num'")) {
				$stmt->execute();
				$stmt->bind_result($userCount);
				$stmt->fetch();
				$stmt->close();
			}
		}
		
		$userId = $num;
		
		// Insert the new user into the database
		if ($insert_stmt = $mysqli->prepare($isPatient ? "INSERT INTO patients (patient_id, username, email, password, salt, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?)" : "INSERT INTO physicians (physician_id, username, email, password, salt, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
			$insert_stmt->bind_param('issssss', $userId, $username, $email, $password, $random_salt, $firstName, $lastName);
			// Execute the prepared query.
			if (!$insert_stmt->execute()) {
				header('Location: ../error.php?err=Registration failure: INSERT');
			}

			// If the user is not a patient, i.e., a physician, create a directory for file uploads
			if (!$isPatient) {
				//$userId = $mysqli->insert_id;
				if (!(mkdir(UPLOADS_LOCATION.$userId))) {
					echo('Error creating upload directory for user.');
				}
			}
			
			if ($isPatient) {
				//$userId = $mysqli->insert_id;
				if ($userId == 0)
					header('Location: ../error.php?err=Registration failure: GET INSERT ID');
				$insert_stmt = $mysqli->prepare("INSERT INTO patient_physicians (id, physician_id) VALUES (?, ?)");
				$insert_stmt->bind_param('ii', $userId, $physId);
				if (!$insert_stmt->execute()) {
					header('Location: ../error.php?err=Registration failure: INSERT ID PAIR');
				}
				if (file_exists(UPLOADS_LOCATION.$physId)) {
					if (!(mkdir(UPLOADS_LOCATION.$physId."/".$userId))) {
						echo('Error creating patient directory');
					}
				} else {
					echo('ERROR: physician directory does not exist');
				}
			}
		} else {
			header('Location: ../error.php?err=Registration failure: STATEMENT PREPARATION FAILED');
		}
		header('Location: ../register_success.php');
	}
}
?>


