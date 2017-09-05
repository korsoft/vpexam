<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";

if (!isset($_POST['physId']) || !isset($_POST['content'])) {
    $errorMsg = "Invalid parameters";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit;
}

$physId = $_POST['physId'];
$content = $_POST['content'];

$prepStmtCheckExists = "SELECT id FROM physician_prefs WHERE id = ?";
$stmtCheckExists = $mysqli->prepare($prepStmtCheckExists);
if ($stmtCheckExists) {
    $stmtCheckExists->bind_param('i', $physId);
    $stmtCheckExists->execute();
    $stmtCheckExists->store_result();

    // In this case perform an update
    if ($stmtCheckExists->num_rows > 0) {
        $stmtCheckExists->close();
        $prepStmtUpdateNormal = "UPDATE physician_prefs SET normal = ? WHERE id = ?";
        $stmtUpdateNormal = $mysqli->prepare($prepStmtUpdateNormal);
        if ($stmtUpdateNormal) {
            $stmtUpdateNormal->bind_param('si', $content, $physId);
            $stmtUpdateNormal->execute();
            $stmtUpdateNormal->close();

            $errorMsg = "";
            $success = true;
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
            exit;
        } else {
            $errorMsg = "Error preparing 'update normal' statement";
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
            exit;
        }
    } else {
        // In this case perform an insert
        $stmtCheckExists->close();
        $prepStmtInsertNormal = "INSERT INTO physician_prefs(id, normal) VALUES(?, ?)";
        $stmtInsertNormal = $mysqli->prepare($prepStmtInsertNormal);
        if ($stmtInsertNormal) {
            $stmtInsertNormal->bind_param('is', $physId, $content);
            $stmtInsertNormal->execute();
            $stmtInsertNormal->close();

            $errorMsg = "";
            $success = true;
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
            exit;
        } else {
            $errorMsg = "Error preparing 'insert normal' statement";
            echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
            exit;
        }
    }
} else {
    $errorMsg = "Error preparing 'check exists' statement";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit;
}
?>