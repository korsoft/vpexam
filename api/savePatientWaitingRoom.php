<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';
//API para guardar pre-registro y despues enviar a waiting room ya logueada, 

$success = false;
$errorMsg = "";

error_log('API :: PREREGISTER :: POST { ' . print_r($_POST, true) . ' }');
extract($_POST);
$birthdateFormatted = (new DateTime($birthdate, new DateTimeZone("UTC")))->format('Y-m-d');

//$randomSalt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
// Create salted password
//$password = hash('sha512', $password . $randomSalt);
//Mandar llamar funcion de random salt
$havePwd = isset($password);
if ($havePwd) {
    $arrPass = hashPassword($password);
    $pwd_pat = $arrPass['pwd'];
    $salt_pat = $arrPass['randomSalt'];
}

if (empty($name) || empty($lastname) || empty($birthdateFormatted) || empty($email) || empty($physicianid)) {
    $errorMsg = "One or more required parameters was not set.";
    echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    exit();
}else{
    //error_log("API :: PREREGISTER2 :: POST { $name } CALL sp_register_patient('{$name}','{$lastname}','{$birthdateFormatted}','{$email}','{$password}','{$salt}',{$physicianid});");
    $result = $mysqli->query("CALL sp_register_patient('{$name}','{$lastname}','{$birthdateFormatted}','{$email}','{$pwd_pat}','{$salt_pat}',{$physicianid});");
    while ($row = $result->fetch_array()){
        $success = true;
        echo( json_encode(array("errorMsg" => $errorMsg, "success" => $success, "patient_id" =>$row['patient_id'] )));
    }

    
    exit();  
}
echo(json_encode(array("errorMsg" => $errorMsg, "success" => $success)));
exit(); 
?>