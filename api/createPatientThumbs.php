<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../libs/ImageResize.php';

use \Eventviva\ImageResize;
use \Eventviva\ImageResizeException;

const BASE_PATH_PATIENTS = '/var/www/.uploads/profile/patients/img/';
$success = false;
$errorMsg = "";


$result = $mysqli->query("SELECT patient_id FROM patients;");
while ($row = $result->fetch_array()){
    $success = true;
    //echo( json_encode(array("errorMsg" => $errorMsg, "success" => $success, "patient_id" =>$row['patient_id'] )));
    $userId = $row['patient_id'];
    $file = BASE_PATH_PATIENTS . $userId . '.png';
    $fileoriginal = BASE_PATH_PATIENTS . $userId . '_original.png';
    //error_log('message::::::'.$file);
    if(file_exists($file) && imagecreatefrompng($file)){
        rename($file, $fileoriginal);
        //Crear thumb profile
        error_log('message:::::: EXISTE '.$file);
        $image = new ImageResize($fileoriginal);
        $image->crop(250, 250);
        $image->save(BASE_PATH_PATIENTS .$userId.'_profile.png');

        //Crear thumb waiting room
        $image2 = new ImageResize($fileoriginal);
        $image2->crop(65, 65);
        $image2->save(BASE_PATH_PATIENTS .$userId.'.png');
    }
}
$errorMsg='Fin de todo';
echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
exit(); 
?>