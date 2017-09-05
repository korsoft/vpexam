<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
 
sec_session_start();

function getPhysiciansAPI($token, $mysqli) {
	$prepStmtCheckToken = "SELECT id FROM tokens WHERE token = ? AND token_name = 'api_token'";
	$stmtCheckToken = $mysqli->prepare($prepStmtCheckToken);
	if ($stmtCheckToken) {
		$stmtCheckToken->bind_param('s', $token);
		$stmtCheckToken->execute();
		$stmtCheckToken->store_result();
		
		if ($stmtCheckToken->num_rows > 0) {
			$stmtCheckToken->close();
			// We've got a valid token so we can proceed with getting
			// the list of physicians to return
			$prepStmtGetPhysicians = "SELECT physician_id, first_name, last_name FROM physicians";
			$stmtGetPhysicians = $mysqli->prepare($prepStmtGetPhysicians);
			if ($stmtGetPhysicians) {
				$allPhysicians = [];
				$id = -1;
				$fname = "";
				$lname = "";
				$stmtGetPhysicians->execute();
				$stmtGetPhysicians->bind_result($id, $fname, $lname);
				while ($stmtGetPhysicians->fetch()) {
					$physician = array("id" => $id, "firstName" => $fname, "lastName" =>$lname);
					array_push($allPhysicians, $physician);
				}
				$stmtGetPhysicians->close();
				
				// Now return the array of physicians
				$errorMsg = "";
				$physicians = $allPhysicians;
				$array = array(
					"errorMsg" => $errorMsg,
					"physicians" => $physicians
				);
				return $array;
			} else {
				$errorMsg = "Error preparing SQL statement";
				$physicians = [];
				$array = array(
					"errorMsg" => $errorMsg,
					"physicians" => $physicians
				);
				return $array;
			}
		} else {
			$errorMsg = "Invalid token";
			$physicians = [];
			$array = array(
				"errorMsg" => $errorMsg,
				"physicians" => $physicians
			);
			$stmtCheckToken->close();
			return $array;
		}
	} else {
		$errorMsg = "Error preparing SQL statement";
		$physicians = [];
		$array = array(
			"errorMsg" => $errorMsg,
			"physicians" => $physicians
		);
		return $array;
	}
}

if (isset($_POST['token'])) {
	$arr = getPhysiciansAPI($_POST['token'], $mysqli);
	echo json_encode($arr);
} else {
	$errorMsg = "Token not present in POST parameters";
	$physicians = [];
	$array = array(
		"errorMsg" => $errorMsg,
		"physicians" => $physicians
	);
	echo json_encode($array);
}
?>