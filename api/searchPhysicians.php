<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../includes/psl-config.php';

sec_session_start();

class PhysicianInfoResult {
    public $physicianId = -1;
    public $email = "";
    public $firstName = "";
    public $middleName = "";
    public $lastName = "";
    public $practiceName = "";
    public $practiceAddress = "";
    public $practiceCity = "";
    public $practiceState = "";
    public $practiceZip = "";

    function __construct($id, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip) {
        $this->physicianId = $id;
        $this->email = $email;
        $this->firstName = $fname;
        $this->middleName = $mname;
        $this->lastName = $lname;
        $this->practiceName = $pname;
        $this->practiceAddress = $paddr;
        $this->practiceCity = $pcity;
        $this->practiceState = $pstate;
        $this->practiceZip = $pzip;
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
$physId = -1;
$email = "";
$fname = "";
$mname = "";
$lname = "";
$pname = "";
$paddr = "";
$pcity = "";
$pstate = "";
$pzip = "";
if (count($tokens) === 1) {
    $prepStmtPhysSearch = "SELECT physician_id, email, first_name, middle_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE first_name RLIKE ? OR middle_name RLIKE ? OR last_name RLIKE ?";
    $stmtPhysSearch = $mysqli->prepare($prepStmtPhysSearch);
    if ($stmtPhysSearch) {
        $stmtPhysSearch->bind_param('sss', $tokens[0], $tokens[0], $tokens[0]);
        $stmtPhysSearch->execute();
        $stmtPhysSearch->bind_result($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        while ($stmtPhysSearch->fetch())
            $results[] = new PhysicianInfoResult($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        $stmtPhysSearch->close();

    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL query.";
        $results = [];
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
        exit();
    }
} else if (count($tokens) === 2) {
    $prepStmtPhysSearch = "SELECT physician_id, email, first_name, middle_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE (first_name RLIKE ? AND middle_name RLIKE ?) OR (first_name RLIKE ? AND last_name RLIKE ?)";
    $stmtPhysSearch = $mysqli->prepare($prepStmtPhysSearch);
    if ($stmtPhysSearch) {
        $stmtPhysSearch->bind_param('ssss', $tokens[0], $tokens[1], $tokens[0], $tokens[1]);
        $stmtPhysSearch->execute();
        $stmtPhysSearch->bind_result($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        while ($stmtPhysSearch->fetch())
            $results[] = new PhysicianInfoResult($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        $stmtPhysSearch->close();

    } else {
        $success = false;
        $errorMsg = "Error preparing MySQL query.";
        $results = [];
        echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg, "results" => $results)));
        exit();
    }
} else if (count($tokens) === 3) {
    $prepStmtPhysSearch = "SELECT physician_id, email, first_name, middle_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE first_name RLIKE ? AND middle_name RLIKE ? AND last_name RLIKE ?";
    $stmtPhysSearch = $mysqli->prepare($prepStmtPhysSearch);
    if ($stmtPhysSearch) {
        $stmtPhysSearch->bind_param('sss', $tokens[0], $tokens[1], $tokens[2]);
        $stmtPhysSearch->execute();
        $stmtPhysSearch->bind_result($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        while ($stmtPhysSearch->fetch())
            $results[] = new PhysicianInfoResult($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        $stmtPhysSearch->close();

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
    $prepStmtPhysSearch = "SELECT physician_id, email, first_name, middle_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE first_name RLIKE ? OR middle_name RLIKE ? OR last_name RLIKE ?";
    $stmtPhysSearch = $mysqli->prepare($prepStmtPhysSearch);
    if ($stmtPhysSearch) {
        $stmtPhysSearch->bind_param('sss', $tokens[0], $tokens[1], $tokens[2]);
        $stmtPhysSearch->execute();
        $stmtPhysSearch->bind_result($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        while ($stmtPhysSearch->fetch())
            $results[] = new PhysicianInfoResult($physId, $email, $fname, $mname, $lname, $pname, $paddr, $pcity, $pstate, $pzip);
        $stmtPhysSearch->close();

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