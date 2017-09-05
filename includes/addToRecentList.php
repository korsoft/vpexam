<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$success = false;
$error = "";
$recentList = "";

if (isset($_GET['physId']) && isset($_GET['patientId'])) {
    $physId = $_GET['physId'];
    $patientId = $_GET['patientId'];
    $prepStmtGetRecentList = "SELECT recently_viewed FROM physicians WHERE physician_id = ?";
    $stmtGetRecentList = $mysqli->prepare($prepStmtGetRecentList);
    if ($stmtGetRecentList) {
        $stmtGetRecentList->bind_param('i', $physId);
        $stmtGetRecentList->execute();
        $stmtGetRecentList->bind_result($recentList);
        $stmtGetRecentList->fetch();
        $stmtGetRecentList->close();
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "error" => $error)));
        exit();
    }

    // Convert JSON object to PHP associative array
    $recentList = json_decode($recentList);

    // There is no recent list yet, new physician
    $newList = [];
    if (is_null($recentList) || count($recentList) == 0) {
        $recentList = [];
        $recentList[0] = $patientId;
        $newList = $recentList;
    } else {
        if (count($recentList) == 10) {
            // If the recents list already has 10 items, remove item 10, and
            // add the new one at position 0.
            if (in_array($patientId, $recentList, true)) {
                // If the ID already exists in the array, move it to the beginning
                $idx = array_search($patientId, $recentList, true);
                if (!($idx === false)) {
                    // If item is already at position 0, there's no need to swap.
                    if (!($idx === 0)) {
                        $newList[0] = $recentList[$idx];
                        $i = 1;
                        foreach($recentList as $key => $value) {
                            // Exclude the element we moved to position 0
                            if ($idx == $key) {
                            } else {
                                $newList[$i] = $value;
                                $i++;
                            }
                        }
                    } else {
                        // Nothing needs to be changed
                        $newList = $recentList;
                    }
                }
            } else {
                // Add the new element at position, and drop the
                // last element
                echo("Line: " . __LINE__);
                $newList[0] = $patientId;
                $i = 1;
                for ($i = 0; $i < count($recentList) - 1; $i++)
                    $newList[$i + 1] = $recentList[$i];
            }
        } else {
            if (in_array($patientId, $recentList, true)) {
                // If the ID already exists in the array, move it to the beginning
                $idx = array_search($patientId, $recentList, true);
                if (!($idx === false)) {
                    // If item is already at position 0, there's no need to swap.
                    if (!($idx === 0)) {
                        $newList[0] = $recentList[$idx];
                        $i = 1;
                        foreach($recentList as $key => $value) {
                            // Exclude the element we moved to position 0
                            if ($idx == $key) {
                            } else {
                                $newList[$i] = $value;
                                $i++;
                            }
                        }
                    } else {
                        // Nothing needs to be changed
                        $newList = $recentList;
                    }
                }
            } else {
                $newList[0] = $patientId;
                $i = 1;
                for ($i = 0; $i < count($recentList); $i++)
                    $newList[$i + 1] = $recentList[$i];
            }
        }
    }

    $newList = json_encode($newList);

    // Now, add the new list to the DB
    $prepStmtUpdateRecentList = "UPDATE physicians SET recently_viewed = ? WHERE physician_id = ?";
    $stmtUpdateRecentList = $mysqli->prepare($prepStmtUpdateRecentList);
    if ($stmtUpdateRecentList) {
        $stmtUpdateRecentList->bind_param('si', $newList, $physId);
        $stmtUpdateRecentList->execute();
        $stmtUpdateRecentList->close();
    } else {
        $success = false;
        $error = "Error preparing MySQL statement.";
        echo(json_encode(array("success" => $success, "error" => $error)));
        exit();
    }

    $success = true;
    $error = "";
    echo(json_encode(array("success" => $success, "error" => $error)));
} else {
    $success = false;
    $error = "Invalid or missing parameters.";
    echo(json_encode(array("success" => $success, "error" => $error)));
}
?>