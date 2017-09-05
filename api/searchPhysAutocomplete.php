<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../includes/psl-config.php';

sec_session_start();

$success = false;
$errorMsg = "";
$results = [];

$keywords = $_GET['keywords'];

if (!isset($keywords)) {
    $success = false;
    $errorMsg = "Missing 'keywords' parameter";
    $results = [];
    echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
} else {
    $keywords = json_decode($keywords);
    // If there are two keywords, treat the first one as the first name and the
    // seconds one as the last name.
    if (count($keywords) === 1) {
        // In this case the single keyword could either be the first name or
        // the last name, so it will be treated as both.
        $keyword = $keywords[0];
        $prepStmt = "SELECT physician_id, first_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE first_name RLIKE ? or last_name RLIKE ?";
        $stmt = $mysqli->prepare($prepStmt);
        if ($stmt) {
            $id = -1;
            $fname = "";
            $lname = "";
            $practiceName = "";
            $practiceAddr = "";
            $practiceCity = "";
            $practiceState = "";
            $practiceZip = "";
            $stmt->bind_param('ss', $keyword, $keyword);
            $stmt->execute();
            $stmt->bind_result($id, $fname, $lname, $practiceName, $practiceAddr, $practiceCity, $practiceState, $practiceZip);
            while ($stmt->fetch()) {
                $practiceInfo = [];
                $practiceInfo['name'] = $practiceName;
                $practiceInfo['addr'] = $practiceAddr;
                $practiceInfo['city'] = $practiceCity;
                $practiceInfo['state'] = $practiceState;
                $practiceInfo['zip'] = $practiceZip;
                $phys = array("id" => $id, "fname" => $fname, "lname" => $lname, "practiceInfo" => $practiceInfo);
                $results[] = $phys;
            }
            $stmt->close();
        } else {
            $success = false;
            $errorMsg = "Error preparing search SQL statement.";
            $results = [];
            echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
            exit();
        }
    } else if (count($keywords) === 2) {
        $keywordFname = $keywords[0];
        $keywordLname = $keywords[1];
        $prepStmt = "SELECT physician_id, first_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE first_name RLIKE ? AND last_name RLIKE ?";
        $stmt = $mysqli->prepare($prepStmt);
        if ($stmt) {
            $id = -1;
            $fname = "";
            $lname = "";
            $practiceName = "";
            $practiceAddr = "";
            $practiceCity = "";
            $practiceState = "";
            $practiceZip = "";
            $stmt->bind_param('ss', $keywordFname, $keywordLname);
            $stmt->execute();
            $stmt->bind_result($id, $fname, $lname, $practiceName, $practiceAddr, $practiceCity, $practiceState, $practiceZip);
            while ($stmt->fetch()) {
                $practiceInfo = [];
                $practiceInfo['name'] = $practiceName;
                $practiceInfo['addr'] = $practiceAddr;
                $practiceInfo['city'] = $practiceCity;
                $practiceInfo['state'] = $practiceState;
                $practiceInfo['zip'] = $practiceZip;
                $phys = array("id" => $id, "fname" => $fname, "lname" => $lname, "practiceInfo" => $practiceInfo);
                $results[] = $phys;
            }
            $stmt->close();
        } else {
            $success = false;
            $errorMsg = "Error preparing search SQL statement.";
            $results = [];
            echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
            exit();
        }
    } else if (count($keywords) >= 3) {
        // If there are 3 or more keywords, treat the first 2 as the first name
        // (aka a Double Name - see: http://en.wikipedia.org/wiki/Double_name)
        // The 3rd keyword is assumed to be the last name, and any other keywords are ignored.
        $keywordFname = $keywords[0] . " " . $keywords[1];
        $keywordLname = $keywords[2];
        $prepStmt = "SELECT physician_id, first_name, last_name, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE first_name RLIKE ? AND last_name RLIKE ?";
        $stmt = $mysqli->prepare($prepStmt);
        if ($stmt) {
            $id = -1;
            $fname = "";
            $lname = "";
            $practiceName = "";
            $practiceAddr = "";
            $practiceCity = "";
            $practiceState = "";
            $practiceZip = "";
            $stmt->bind_param('ss', $keywordFname, $keywordLname);
            $stmt->execute();
            $stmt->bind_result($id, $fname, $lname, $practiceName, $practiceAddr, $practiceCity, $practiceState, $practiceZip);
            while ($stmt->fetch()) {
                $practiceInfo = [];
                $practiceInfo['name'] = $practiceName;
                $practiceInfo['addr'] = $practiceAddr;
                $practiceInfo['city'] = $practiceCity;
                $practiceInfo['state'] = $practiceState;
                $practiceInfo['zip'] = $practiceZip;
                $phys = array("id" => $id, "fname" => $fname, "lname" => $lname, "practiceInfo" => $practiceInfo);
                $results[] = $phys;
            }
            $stmt->close();
        } else {
            $success = false;
            $errorMsg = "Error preparing search SQL statement.";
            $results = [];
            echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
            exit();
        }
    }
    $success = true;
    $errorMsg = "";
    echo(json_encode(array("success" => $success, "error" => $errorMsg, "results" => $results)));
}