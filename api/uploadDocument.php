<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';


require_once '../util/JSLikeHTMLElement.php';


// Include the autoloader
require_once '../js/dompdf/autoload.inc.php';

// Reference and use the dompdf class
use Dompdf\Dompdf;

sec_session_start();

$physId = $_POST["physician_id"];
$patientId = $_POST["patient_id"];
$examId = $_POST['examId'];
$nameDocument = $_POST['nameDocument'];
$file = $_FILES['file']['tmp_name'];
$fileType = $_FILES['file']['type'];
$fileSize = $_FILES['file']['size']; //5242880


$success = false;
$errorMsg = "";

if (isset($physId) && isset($patientId) && isset($examId) && isset($nameDocument) && isset($file)) {
	if($fileSize <= 5242880 && ( $fileType=='image/jpeg' || $fileType=='image/png' || $fileType=='image/gif')){
		$targetDir = "/var/www/.uploads/".$physId."/".$patientId."/".$examId."/documents/";

		if (file_exists($targetDir) == false) {
			// Create the directory structure with mode 0777 (all users)
			if (!(mkdir($targetDir, 0777, true))) {
				$success = false;
				$errorMsg = "Error creating target directory.";
				echo json_encode(array("success" => $success, "error" => $errorMsg));
				exit();
			}
		}
		//Call Stored Procedure
	    $result = $mysqli->query("CALL sp_insert_exam_document({$examId},'{$nameDocument}');");
	    //obtener filename
	    $row = $result->fetch_array();
	    $filename = $row['file'];
	  

	    //Create PDF 
		$dompdf = new DOMPDF();
		$html = '<html>'.
			 '<body>'.
			  '<h1>'.$nameDocument.'</h1> <br/>'.
			  '<img src="'.$file.'" style="max-width: 720px; height: auto; max-height: 90%;" />'.
			 '</body>'.
			'</html>';

		$dompdf->load_html($html);
		$dompdf->render();

		$output = $dompdf->output();
		file_put_contents($targetDir.$filename, $output);
	}else {
	    $success = false;
	    $errorMsg = "The filesize exceeds the limit of 5242880 bytes or image type is incorrect 4.";
	    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
	    exit();
	}
}else {
    $success = false;
    $errorMsg = "One or more of the required parameters are missing 3.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}
$success = true;
$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));