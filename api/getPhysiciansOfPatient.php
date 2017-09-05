<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

function getPhysiciansAPI($token, $patientId, $mysqli) {
    $prepStmtCheckToken = "SELECT id FROM tokens WHERE token = ? AND name = 'api_token'";
    $stmtCheckToken = $mysqli->prepare($prepStmtCheckToken);
    if ($stmtCheckToken) {
        $stmtCheckToken->bind_param('s', $token);
        $stmtCheckToken->execute();
        $stmtCheckToken->store_result();

        if ($stmtCheckToken->num_rows > 0) {
            $stmtCheckToken->close();
            // We've got a valid token so we can proceed with getting
            // the list of physicians to return
            $physIds = [];
            $prepStmtGetPhysIdsOfPatient = "SELECT physician_id FROM patient_physicians WHERE id = ?";
            $stmtGetPhysIdsOfPatient = $mysqli->prepare($prepStmtGetPhysIdsOfPatient);
            if ($stmtGetPhysIdsOfPatient) {
                $id = -1;
                $stmtGetPhysIdsOfPatient->bind_param('i', $patientId);
                $stmtGetPhysIdsOfPatient->execute();
                $stmtGetPhysIdsOfPatient->bind_result($id);
                while ($stmtGetPhysIdsOfPatient->fetch())
                    $physIds[] = $id;
                $stmtGetPhysIdsOfPatient->close();
            } else {
                $errorMsg = "Error preparing SQL statement";
                $physicians = [];
                $array = array(
                    "errorMsg" => $errorMsg,
                    "physicians" => $physicians
                );
                return $array;
            }

            $num = count($physIds);
            $physicians = [];
            for ($i = 0; $i < $num; $i++) {
                $prepStmtGetPhysicianInfo = "SELECT physician_id, npi, first_name, middle_name, last_name, username, gender, dob, practice_name, practice_addr, practice_city, practice_state, practice_zip FROM physicians WHERE physician_id = ?";
                $stmtGetPhysicianInfo = $mysqli->prepare($prepStmtGetPhysicianInfo);
                if ($stmtGetPhysicianInfo) {
                    $id = -1;
                    $npi = "";
                    $fname = "";
                    $mname = "";
                    $lname = "";
                    $gender = "";
                    $dob = "";
                    $pName = "";
                    $pAddr = "";
                    $pCity = "";
                    $pState = "";
                    $pZip = "";
                    $stmtGetPhysicianInfo->bind_param('i', $physIds[$i]);
                    $stmtGetPhysicianInfo->execute();
                    $stmtGetPhysicianInfo->bind_result($id, $npi, $fname, $mname, $lname, $gender, $dob, $pName, $pAddr, $pCity, $pState, $pZip);
                    $stmtGetPhysicianInfo->fetch();
                    $physicians[] = array("physicianId" => $id, "npi" => $npi, "firstName" => $fname, "middleName" => $mname, "lastName" => $lname, "gender" => $gender, "dob" => $dob, "practiceName" => $pName, "practiceAddress" => $pAddr, "practiceCity" => $pCity, "practiceState" => $pState, "practiceZip" => $pZip);
                    $stmtGetPhysicianInfo->close();
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

            $errorMsg = "";
            $array = array(
                "errorMsg" => $errorMsg,
                "physicians" => $physicians
            );
            return $array;
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

if (isset($_POST['token'], $_POST['patientId'])) {
    $arr = getPhysiciansAPI($_POST['token'], $_POST['patientId'], $mysqli);
    echo json_encode($arr);
} else {
    $errorMsg = "One or more of the required POST parameters are not set.";
    $physicians = [];
    $array = array(
        "errorMsg" => $errorMsg,
        "physicians" => $physicians
    );
}
?>