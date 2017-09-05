<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

class PatientSearchResult {
    public $assocPhys = [];
    public $patientId = -1;
    public $firstName = "";
    public $middleName = "";
    public $lastName = "";
    public $email = "";
    public $gender = "";
    public $dob = "";
    public $phone = "";

    function __construct($assocPhys, $patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone) {
        $this->assocPhys = $assocPhys;
        $this->patientId = $patientId;
        $this->firstName = $fname;
        $this->middleName = $mname;
        $this->lastName = $lname;
        $this->email = $email;
        $this->gender = $gender;
        $this->dob = $dob;
        $this->phone = $phone;
    }
}

$success = false;
$errorMsg = "";
$results = [];

$query = $_GET['query'];

if (!isset($query)) {
    $success = false;
    $errorMsg = "Required parameter, 'query' is not set.";
    $results = [];
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
    exit();
}

$tokens = explode(" ", $query);
$assocPhys = [];
$patientId = -1;
$fname = "";
$mname = "";
$lname = "";
$gender = "";
$dob = "";
$phone = "";
if (count($tokens) === 1) {
    $prepStmtProfileSearch = "SELECT patient_id, first_name, middle_name, last_name, email, gender, dob, phone FROM patients WHERE first_name RLIKE ? OR middle_name RLIKE ? OR last_name RLIKE ?";
    $stmtProfileSearch = $mysqli->prepare($prepStmtProfileSearch);
    if ($stmtProfileSearch) {
        $stmtProfileSearch->bind_param('sss', $tokens[0], $tokens[0], $tokens[0]);
        $stmtProfileSearch->execute();
        $stmtProfileSearch->bind_result($patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        while ($stmtProfileSearch->fetch())
            $results[] = new PatientSearchResult($assocPhys, $patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        $stmtProfileSearch->close();

        for ($i = 0; $i < count($results); $i++) {
            $assocPhys = getPhysiciansOfPatient($results[$i]->patientId, $mysqli);
            $results[$i]->assocPhys = $assocPhys;
        }
    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL query.";
        $results = [];
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
        exit();
    }
} else if (count($tokens) === 2) {
    $prepStmtProfileSearch = "SELECT patient_id, first_name, middle_name, last_name, email, gender, dob, phone FROM patients WHERE (first_name RLIKE ? AND middle_name RLIKE ?) OR (first_name RLIKE ? AND last_name RLIKE ?)";
    $stmtProfileSearch = $mysqli->prepare($prepStmtProfileSearch);
    if ($stmtProfileSearch) {
        $stmtProfileSearch->bind_param('ssss', $tokens[0], $tokens[1], $tokens[0], $tokens[1]);
        $stmtProfileSearch->execute();
        $stmtProfileSearch->bind_result($patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        while ($stmtProfileSearch->fetch())
            $results[] = new PatientSearchResult($assocPhys, $patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        $stmtProfileSearch->close();

        for ($i = 0; $i < count($results); $i++) {
            $assocPhys = getPhysiciansOfPatient($results[$i]->patientId, $mysqli);
            $results[$i]->assocPhys = $assocPhys;
        }
    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL query.";
        $results = [];
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
        exit();
    }
} else if (count($tokens) === 3) {
    $prepStmtProfileSearch = "SELECT patient_id, first_name, middle_name, last_name, email, gender, dob, phone FROM patients WHERE first_name RLIKE ? AND middle_name RLIKE ? AND last_name RLIKE ?";
    $stmtProfileSearch = $mysqli->prepare($prepStmtProfileSearch);
    if ($stmtProfileSearch) {
        $stmtProfileSearch->bind_param('sss', $tokens[0], $tokens[1], $tokens[2]);
        $stmtProfileSearch->execute();
        $stmtProfileSearch->bind_result($patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        while ($stmtProfileSearch->fetch())
            $results[] = new PatientSearchResult($assocPhys, $patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        $stmtProfileSearch->close();

        for ($i = 0; $i < count($results); $i++) {
            $assocPhys = getPhysiciansOfPatient($results[$i]->patientId, $mysqli);
            $results[$i]->assocPhys = $assocPhys;
        }
    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL query.";
        $results = [];
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
        exit();
    }
} else if (count($tokens) > 3) {
    // If the number of tokens is greater than three, only take the first three
    // and search the db using OR rather than AND
    $prepStmtProfileSearch = "SELECT patient_id, first_name, middle_name, last_name, email, gender, dob, phone FROM patients WHERE first_name RLIKE ? OR middle_name RLIKE ? OR last_name RLIKE ?";
    $stmtProfileSearch = $mysqli->prepare($prepStmtProfileSearch);
    if ($stmtProfileSearch) {
        $stmtProfileSearch->bind_param('sss', $tokens[0], $tokens[1], $tokens[2]);
        $stmtProfileSearch->execute();
        $stmtProfileSearch->bind_result($patientId, $fname, $mname, $lname, $email, $dob, $phone);
        while ($stmtProfileSearch->fetch())
            $results[] = new PatientSearchResult($assocPhys, $patientId, $fname, $mname, $lname, $email, $gender, $dob, $phone);
        $stmtProfileSearch->close();

        for ($i = 0; $i < count($results); $i++) {
            $assocPhys = getPhysiciansOfPatient($results[$i]->patientId, $mysqli);
            $results[$i]->assocPhys = $assocPhys;
        }
    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL query.";
        $results = [];
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
        exit();
    }
}

$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
?>