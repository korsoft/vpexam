<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
include_once '../libs/ImageResize.php';

use \Eventviva\ImageResize;
use \Eventviva\ImageResizeException;

//API para guardar pre-registro y despues enviar a waiting room ya logueada, 
const BASE_PATH_PATIENTS = '/var/www/.uploads/profile/patients/img/';
$success = false;
$errorMsg = "";

error_log('API :: PREREGISTER :: POST { ' . print_r($_POST, true) . ' }');
extract($_POST);
$birthdateFormatted = (new DateTime($birthdate, new DateTimeZone("UTC")))->format('Y-m-d');
$havePwd = isset($password);
if ($havePwd) {
    $arrPass = hashPassword($password);
    $pwd_pat = $arrPass['pwd'];
    $salt_pat = $arrPass['randomSalt'];
}
if(!isset($email)){ 
    $email='';
    $pwd_pat='';
    $salt_pat='';
}
if(!isset($phone)){ 
    $phone='';
}
if(empty($username) ){
    //construir
    $bd=str_replace('-', '', $birthdate); 
    error_log($birthdate);
    error_log($birthdateFormatted);
    $username=$name.$lastname.$bd.$gender;
    error_log($username);
}
$username = str_replace(' ', '', $username);

if (empty($name) || empty($lastname) || empty($birthdateFormatted) || empty($gender) ||  empty($physicianid)) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}else{
    if ($gender == 'M'){$gender="male";}else{$gender="female";}
    //error_log("API :: PREREGISTER2 :: POST { $name } CALL sp_register_patient('{$name}','{$lastname}','{$birthdateFormatted}','{$email}','{$password}','{$salt}',{$physicianid});");
    $result = $mysqli->query("CALL sp_register_patient('{$username}','{$name}','{$lastname}','{$birthdateFormatted}','{$gender}','{$phone}','{$email}','{$pwd_pat}','{$salt_pat}',{$physicianid});");
    while ($row = $result->fetch_array()){
        $success = true;
        echo( json_encode(array("errorMsg" => $errorMsg, "success" => $success, "patient_id" =>$row['patient_id'] )));
        $userId = $row['patient_id'];
    }
    //Guardar imagen de profile si la trae
    if (!empty($photo)) {
        error_log("Profile picture uploaded. FILES parameter set.");
        // We have a file upload. Retrieve it and move it to the proper directory.
        $img = preg_replace('/data:image\/(png|jpeg|jpg|gif);base64,/', '', $photo);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file = BASE_PATH_PATIENTS . $userId . '_original.png';
        $success = file_put_contents($file, $data);

        //$image = new ImageResize($file);
        $image =  ImageResize::createFromString(base64_decode($img));
        $image->crop(250, 250);
        $image->save(BASE_PATH_PATIENTS .$userId.'_profile.png');

        //$image2 = new ImageResize($file);
        $image2 = ImageResize::createFromString(base64_decode($img));
        $image2->crop(65, 65);
        $image2->save(BASE_PATH_PATIENTS .$userId.'.png');

        //error_log("Dest file location: " . $file);
    } else {
        error_log("No profile picture uploaded. FILES parameter not set." . $photo);
    }
    
    exit();  
}
echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
exit(); 
?>