<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";

extract($_POST);

$sort=0;
$idresult=0;

if (empty($title) || empty($abbrev) || empty($description) || empty($type) || $author_physician==0) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(!preg_match("/^[a-zA-Z0-9 \-\_\.\:]+$/",$title))
{
    $errorMsg = "Please input alphanumeric characters only.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(2048<strlen($title))
{
    $errorMsg = "Exam component title must be less than 2048 characters.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(50<strlen($abbrev))
{
    $errorMsg = "Exam component abbrev must be less than 50 characters.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
elseif(4096<strlen($description))
{
    $errorMsg = "Exam component description must be less than 4096 characters.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
else {
    $sql = "CALL sp_update_exam_component($id,'$title','$type','$abbrev','$description',$sort,$public,$author_physician);";    
    if($id==0)/*insert*/
    {
        $sql = "CALL sp_insert_exam_component('$title','$type','$abbrev','$description',$sort,$public,$author_physician);";
    }
    
    if ($resultUPD = $mysqli->query($sql)) {
        $objResult = $resultUPD->fetch_object();
        if(0<$objResult->idresult)
        {
            $success = true;
            $idresult=$objResult->idresult;
        }
        else {
            $errorMsg = "Exam component already exists.";
        }        
    }
    else {
        $errorMsg = "Error preparing mysql statement.";
    }    
    
    echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success, "id" => $idresult)));
    exit();    
}
?>