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

$response = [];
try {
	if(empty($_SESSION['user_id'])) {
		throw new Exception('empty user id', 1);
	}
	if(0 == count($_FILES)) {
		throw new Exception('There is no file to upload', 2);
	}
	$filetype = $_FILES['files']['type'][0];
	if (!preg_match('/^(image)\/(png|jpeg|jpg)$/', $filetype, $matches)) {
		throw new Exception("Invalid image type: $filetype", 3);
	}
	$filesize = $_FILES['files']['size'][0];
	//500000 bytes = 500 KB
	if(0 >= $filesize || 1048576 < $filesize) {
		throw new Exception('Invalid file size: ' . FileSizeConvert($filesize), 4);
	}
	$filetmp = $_FILES['files']['tmp_name'][0];
	$folder="/var/www/.uploads/profile/patients/img/{$_SESSION['user_id']}.png";
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