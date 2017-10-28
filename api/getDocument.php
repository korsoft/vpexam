<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();


$examId = $_POST['examId'];



$success = false;
$errorMsg = "";

if (isset($examId)) {
	
		//$targetDir = "/var/www/.uploads/".$physId."/".$patientId."/".$examId."/documents/";

		//Call Stored Procedure
	    $result = $mysqli->query("CALL sp_select_exam_documents({$examId});");
	    //obtener filename
	    $allDocuments = [];
	    $id = -1;
		$filename = "";
		$name_document = "";
	    while ($row = $result->fetch_array()){
	    	 $document = array(
		        "id" => $row['id'],
		        "filename" => $row['filename'],
		        "name_document" => $row['name_document']
		    );
	    	 array_push($allDocuments, $document);
	    }
	   //$filename = $row['file'];
	    // id | filename | name_document  
	    $documents = $allDocuments;
				$array = array(
					"errorMsg" => $errorMsg,
					"documents" => $documents
				);
				//return $array;
	   
    
	  

	
}else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
$success = true;
$errorMsg = "";
//echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
echo json_encode($array);