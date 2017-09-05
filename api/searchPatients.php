<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../includes/psl-config.php';

sec_session_start();

class SmallPatientInfo {
    public $patientId = -1;
    public $email = "";
    public $firstName = "";
    public $middleName = "";
    public $lastName = "";
    public $mrn = "";
    public $gender = "";
    public $phone = "";
    public $dob = "";
    public $address = "";
    public $city = "";
    public $state = "";
    public $zip = "";

    function __construct($id, $email, $fname, $mname, $lname, $mrn, $gender, $ph, $dob, $addr, $city, $st, $zip) {
        $this->patientId = $id;
        $this->email = $email;
        $this->firstName = $fname;
        $this->middleName = $mname;
        $this->lastName = $lname;
        $this->mrn = $mrn;
        $this->gender = $gender;
        $this->phone = $ph;
        $this->dob = $dob;
        $this->address = $addr;
        $this->city = $city;
        $this->state = $st;
        $this->zip = $zip;
    }
}

class SearchResult {
    public $relevance = 0;
    public $patientId = -1;
    public $patientInfo = NULL;

    function __construct($id, $info) {
        $this->patientId = $id;
        $this->relevance = 1;
        $this->patientInfo = $info;
    }
}

$success = false;
$errorMsg = "";
$results = [];

$keywords = $_GET['keywords'];
$keywords = json_decode($keywords);

$physId = $_GET['physId'];

if (!isset($keywords, $physId)) {
    $success = false;
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
    exit();
} else {
    // Loop through each keyword and compare it against each column in the DB
    $numKeywords = count($keywords);
    for ($i = 0; $i < $numKeywords; $i++) {
        $prepStmtCheckKeyword =
            "SELECT patient_id, email, first_name, middle_name, last_name, mrn, gender, phone, dob, address, city, state, zip
                FROM patients
                LEFT JOIN patient_physicians
                ON patients.patient_id=patient_physicians.id
                WHERE (patients.first_name RLIKE ? OR patients.middle_name RLIKE ? OR patients.last_name RLIKE ? OR patients.mrn RLIKE ? OR patients.address RLIKE ? OR patients.city RLIKE ? OR patients.state RLIKE ? OR patients.zip RLIKE ?)
                    AND patient_physicians.physician_id=?;";

        $stmtCheckKeyword = $mysqli->prepare($prepStmtCheckKeyword);
        if ($stmtCheckKeyword) {
            $ids = [];
            $patientId = -1;
            $email = "";
            $fname = "";
            $mname = "";
            $lname = "";
            $mrn = "";
            $gender = "";
            $phone = "";
            $dob = "";
            $addr = "";
            $city = "";
            $state = "";
            $zip = "";
            $stmtCheckKeyword->bind_param('ssssssssi', $keywords[$i], $keywords[$i], $keywords[$i], $keywords[$i], $keywords[$i], $keywords[$i], $keywords[$i], $keywords[$i], $physId);
            $stmtCheckKeyword->execute();
            $stmtCheckKeyword->bind_result($patientId, $email, $fname, $mname, $lname, $mrn, $gender, $phone, $dob, $addr, $city, $state, $zip);
            while ($stmtCheckKeyword->fetch())
                $ids[] = new SmallPatientInfo($patientId, $email, $fname, $mname, $lname, $mrn, $gender, $phone, $dob, $addr, $city, $state, $zip);
            $stmtCheckKeyword->close();

            for ($j = 0; $j < count($ids); $j++) {
                if (isset($results[$ids[$j]->patientId]))
                    $results[$ids[$j]->patientId]->relevance++;
                else
                    $results[$ids[$j]->patientId] = new SearchResult($ids[$j]->patientId, $ids[$j]);
            }
            unset($ids);
        } else {
            $success = false;
            $errorMsg = "Error preparing MySQL statement.";
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
            exit();
        }
    }

    // Now, we have to go through and find the highest relevance value, and
    // remove all elements that do not have this relevance value.
    reset($results);

    $maxRelevanceVal = 0 < count($results)?$results[key($results)]->relevance:0;
    foreach ($results as $key => $value) {
        if ($results[$key]->relevance > $maxRelevanceVal)
            $maxRelevanceVal = $results[$key]->relevance;
    }

    $filteredResults = [];
    reset($results);
    foreach ($results as $key => $value) {
        if ($results[$key]->relevance === $maxRelevanceVal)
            $filteredResults[] = $value;
    }

    $success = true;
    $errorMsg = "";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $filteredResults)));
}
?>