<?php
include_once 'functions.php';

sec_session_start();

function FileSizeConvert($bytes) {
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = strval(round($result, 2))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

const COMPONENT_IMAGE_PATH  = '/var/www/.uploads/exam/components/';
$response = [];
try {  
	if(empty($_POST['idComponent'])) {
		throw new Exception('empty user id', 1);
	}
	if(empty($_POST['typeFile'])) {
		throw new Exception('empty file type', 1);
	}        
	if(0 == count($_FILES)) {
		throw new Exception('There is no file to upload', 2);
	}
        
        $strFileName = '';
        if($_POST['typeFile']==='M')
        {
            $strFileName = 'male';
            $filetype = $_FILES['fileToUploadMale']['type'];
            $filesize = $_FILES['fileToUploadMale']['size'];
            $filetmp  = $_FILES['fileToUploadMale']['tmp_name'];      
            if (!preg_match('/^(image)\/(png|jpeg|jpg|gif)$/', $filetype, $matches)) {
                    throw new Exception("Invalid image type: $filetype", 3);
            }            
        }
        elseif($_POST['typeFile']==='F')
        {
            $strFileName = 'female';
            $filetype = $_FILES['fileToUploadFemale']['type'];
            $filesize = $_FILES['fileToUploadFemale']['size'];
            $filetmp  = $_FILES['fileToUploadFemale']['tmp_name'];     
            if (!preg_match('/^(image)\/(png|jpeg|jpg|gif)$/', $filetype, $matches)) {
                    throw new Exception("Invalid image type: $filetype", 3);
            }            
        }
        elseif($_POST['typeFile']==='A')
        {
            $strFileName = 'audio';
            $filetype = $_FILES['fileToUploadAudio']['type'];
            $filesize = $_FILES['fileToUploadAudio']['size'];
            $filetmp  = $_FILES['fileToUploadAudio']['tmp_name'];  
            if (!preg_match('/^(audio)\/(mp3|wav|mpeg)$/', $filetype, $matches)) {
                    throw new Exception("Invalid audio type: $filetype", 3);
            }            
        }
	//700000 bytes = 700 KB 
	if(0 >= $filesize ||  700000 < $filesize) {
		throw new Exception('Invalid file size: ' . FileSizeConvert($filesize), 4);
	}
             
       
    if (!file_exists(COMPONENT_IMAGE_PATH.$_POST['idComponent'])) {
        $retVal = mkdir(COMPONENT_IMAGE_PATH.$_POST['idComponent']);
        if (!$retVal) {
            $error = "Temp directory does not exist, and it could not be created.";
            $success = false;
            $returnFilename = "";
            echo(json_encode(array("error" => $error, "success" => $success, "filename" => $returnFilename)));
            exit();
        }
    }
        
	$folder=COMPONENT_IMAGE_PATH.$_POST['idComponent']."/".$strFileName;
	move_uploaded_file($filetmp, $folder);
	$response = ['success' => true];
}
catch(Exception $e) {
	$msg = $e->getMessage();
	$response = ['success' => false, 'error' => ['msg' =>$msg, 'id' => $e->getCode()]];
	error_log("UPLOAD IMAGE :: ERROR : userid { {$_SESSION['user_id']} } : $msg");
}
echo(json_encode($response));
exit();