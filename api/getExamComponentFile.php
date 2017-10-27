<?php
//include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

const FILE_COMPONENT_PATH = '/var/www/.uploads/exam/components/';

if (isset($_GET['physId']) && isset($_GET['modelType'])) {
    //preguntar si existe el archivo male o male_video
    $fileExam = FILE_COMPONENT_PATH.$_GET['physId'].'/'.$_GET['modelType'];
    if(file_exists($fileExam)){

    }elseif(){
         $array = array(
            "error" => "Error preparing MySQL statement."
        );
        echo(json_encode($array));
        exit();
    }

    //$prepStmtGetHospitalId = "SELECT assoc_hospital FROM physicians WHERE physician_id = ?";
    //$stmtGetHospitalId = $mysqli->prepare($prepStmtGetHospitalId);
    if ($stmtGetHospitalId) {
        $hospitalId = -1;
        $stmtGetHospitalId->bind_param('i', $_GET['physId']);
        if (!$stmtGetHospitalId->execute()) {
            $stmtGetHospitalId->close();
            $array = array(
                "error" => "Error while executing MySQL statement."
            );
            echo(json_encode($array));
            exit();
        }
        $stmtGetHospitalId->bind_result($hospitalId);
        $stmtGetHospitalId->fetch();
        $stmtGetHospitalId->close();

        $legalAgreement = FILE_COMPONENT_PATH . $hospitalId . '.pdf';
        if (file_exists($legalAgreement)) {
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($legalAgreement));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($legalAgreement);
        } else {
            // If the hospital does not have a legal agreement, display the default one
            $legalAgreement = FILE_COMPONENT_PATH . 'default.pdf';
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($legalAgreement));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($legalAgreement);
        }
    } else {
        $array = array(
            "error" => "Error preparing MySQL statement."
        );
        echo(json_encode($array));
        exit();
    }

} else {
    $array = array(
        "error" => "Invalid or missing parameters."
    );
    echo(json_encode($array));
}