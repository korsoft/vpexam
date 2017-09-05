<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

parse_str($_SERVER['QUERY_STRING']);

if ($physicianId) {
	$physicianInfo = getPhysicianInfo($physicianId, $mysqli);
	if ($physicianInfo) {
		$array = array(
			"physicianId" => $physicianInfo->physicianId,
			"username" => $physicianInfo->username,
			"firstName" => $physicianInfo->firstName,
			"lastName" => $physicianInfo->lastName,
			"error" => ""
		);
		echo json_encode($array);
	} else {
		$array = array(
			"physicianId" => -1,
			"username" => "",
			"firstName" => "",
			"lastName" => "",
			"error" => "There was an error fetching physicianInfo for the requested physician."
		);
		echo json_encode($array);
	}
} else {
	$array = array(
		"physicianId" => -1,
		"username" => "",
		"firstName" => "",
		"lastName" => "",
		"error" => "Invalid parameters."
	);
	echo json_encode($array);
}
?>
