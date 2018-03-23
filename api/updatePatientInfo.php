<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$success = false;
$errorMsg = "";
$first_name=""; $middle_name = ""; $last_name= ''; $email = ''; $gender = ''; $phone = ''; $dob = ''; $address = ''; $city = '';
$state = ''; $zip = ''; $insurance_company = ''; $insurance_address = ''; $insurance_phone = '';$insurance_ph_name = ''; $ins_pat_relationship = ''; 
$insurance_group_num = ''; $insurance_id_cert_num = ''; $insurance_issue_date = '';
$password = '';$salt = '';
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

try {
	if (empty($_POST['patient_id'])) {
        throw new Exception('One or more required parameters was not set.', 1);
    }else {
    	$patId = $_POST['patient_id'];
    	if ($patId == ''){
	    	error_log('sadasdasd'.$patId );
	    	throw new Exception('The patient id must be an integer.', 2);
    	}
    }
    
    
    
    if (!empty($_POST['first_name'])) {
    	$first_name = $_POST['first_name'];
    	if (strlen($first_name) > 50){
    		throw new Exception('Invalid first name.', 1);
    	}
    	error_log('firstname'. $first_name);
    }
    if (!empty($_POST['middle_name'])) {
    	$middle_name = $_POST['middle_name'];
    	if(strlen($middle_name) > 50){
    		throw new Exception('Invalid middle name.', 1);
    	}
    }
    if (!empty($_POST['last_name'])) {
        $last_name = $_POST['last_name'];
    	if(strlen($last_name) > 50){
    		throw new Exception('Invalid last name.', 1);
    	}
    }
    if (!empty($_POST['email'])) {
    	$email = $_POST['email'];
        if(strlen($email) > 100){
    		throw new Exception('Invalid email.', 1);
    	}else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  throw new Exception('Invalid email.', 2);
		}
    }
    if (!empty($_POST['gender'])) {
        $gender = $_POST['gender'];
        if($gender != 'male' && $gender != 'female'){
    		throw new Exception('Invalid gender.', 1);
    	}
    }
    if (!empty($_POST['phone'])) {
    	$phone = $_POST['phone'];
        if(strlen($phone)!= 10 ){
    		throw new Exception('Invalid phone.', 1);
    	}else if (!preg_match('/[0-9]{10}/', $phone) ) {
    		throw new Exception('Invalid phone.', 2);
    	}
    }
    if (!empty($_POST['dob'])) {
    	$dob = $_POST['dob'];
    	error_log(':::: DOB'. $dob);
        if(strlen($dob)> 12 ){
    		throw new Exception('Invalid date of birth.', 1);
    	}else if (!validateDate($dob)) {
    		throw new Exception('Invalid date of birth.', 2);
    	}
    }
    if (!empty($_POST['address'])) {
    	$address = $_POST['address'];
        if(strlen($address)> 300 ){
    		throw new Exception('Invalid address.', 1);
    	}
    }
    if (!empty($_POST['city'])) {
    	$city = $_POST['city'];
        if(strlen($city)> 200 ){
    		throw new Exception('Invalid city.', 1);
    	}
    }
    if (!empty($_POST['state'])) {
    	$state = $_POST['state'];
        if(strlen($state)> 2 ){
    		throw new Exception('Invalid state.', 1);
    	}
    }
    if (!empty($_POST['zip'])) {
    	$zip = $_POST['zip'];
        if(strlen($zip)> 9 ){
    		throw new Exception('Invalid zip code.', 1);
    	}
    }
    if (!empty($_POST['insurance_company'])) {
    	$insurance_company = $_POST['insurance_company'];
        if(strlen($insurance_company)> 150 ){
    		throw new Exception('Invalid Insurance Company.', 1);
    	}
    }
    if (!empty($_POST['insurance_address'])) {
    	$insurance_address = $_POST['insurance_address'];
        if(strlen($insurance_address)> 300 ){
    		throw new Exception('Invalid Insurance address.', 1);
    	}
    }
    if (!empty($_POST['insurance_phone'])) {
    	$insurance_phone = $_POST['insurance_phone'];
        if(strlen($insurance_phone)> 15 ){
    		throw new Exception('Invalid Insurance phone.', 1);
    	}else if (!preg_match('/[0-9]{10}/', $insurance_phone) ) {
    		throw new Exception('Invalid phone.', 2);
    	}
    }
    if (!empty($_POST['insurance_ph_name'])) {
    	$insurance_ph_name = $_POST['insurance_ph_name'];
        if(strlen($insurance_ph_name)> 150 ){
    		throw new Exception('Invalid Insurante PH name.', 1);
    	}
    }
    if (!empty($_POST['insurance_patient_relationship'])) {
    	$ins_pat_relationship = $_POST['insurance_patient_relationship'];
        if(strlen($ins_pat_relationship)> 30 ){
    		throw new Exception('Invalid Insurance patient relationship.', 1);
    	}
    }
    if (!empty($_POST['insurance_group_num'])) {
    	$insurance_group_num = $_POST['insurance_group_num'];
        if(strlen($insurance_group_num)> 30 ){
    		throw new Exception('Invalid Insurance group number.', 1);
    	}
    }
    if (!empty($_POST['insurance_id_cert_num'])) {
    	$insurance_id_cert_num = $_POST['insurance_id_cert_num'];
        if(strlen($insurance_id_cert_num)> 30 ){
    		throw new Exception('Invalid Insurance id cert num.', 1);
    	}
    }
    if (!empty($_POST['insurance_issue_date'])) {
    	$insurance_issue_date = $_POST['insurance_issue_date'];
        if(strlen($insurance_issue_date)> 10 ){
    		throw new Exception('Invalid Insurance issue date.', 1);
    	}else if (!validateDate($insurance_issue_date)) {
    		throw new Exception('Invalid Insurance issue date.', 2);
    	}
    }
    if (!empty($_POST['password'])) {
        $arrPass = hashPassword($_POST['password']);
        $password = $arrPass['pwd'];
        $salt = $arrPass['randomSalt'];
    }    
    error_log("CALL sp_update_patients_info({$patId},'{$first_name}','{$middle_name}','{$last_name}','{$email}','{$gender}','{$phone}','{$dob}','{$address}','{$city}','{$state}','{$zip}','{$insurance_company}','{$insurance_address}','{$insurance_phone}','{$insurance_ph_name}','{$ins_pat_relationship}','{$insurance_group_num}','{$insurance_id_cert_num}','{$insurance_issue_date}','{$password}','{$salt}');");
	$mysqli->query("CALL sp_update_patients_info({$patId},'{$first_name}','{$middle_name}','{$last_name}','{$email}','{$gender}','{$phone}','{$dob}','{$address}','{$city}','{$state}','{$zip}','{$insurance_company}','{$insurance_address}','{$insurance_phone}','{$insurance_ph_name}','{$ins_pat_relationship}','{$insurance_group_num}','{$insurance_id_cert_num}','{$insurance_issue_date}','{$password}','{$salt}');");
	error_log('::::::: Despues de llamar sp para cambiar settings');
	$success = true;
}
catch(Exception $e) {
	$success = false;
	$errorMsg = $e->getMessage();
	echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
    //error_log('::::::: Error al llamar sp para cambiar settings');
    exit();
}

$errorMsg = "";
echo(json_encode(array("success" => $success, "errorMsg" => $errorMsg)));
exit();
