<?php
include_once 'psl-config.php';

function sec_session_start() {
    /*
	$session_name = 'sec_session_id';	// Set a custom session name
	$secure = SECURE;
	// This stops JavaScript from being able to access the session id.
	$httponly = true;
	// Forces sessions to only use cookies.
	if (ini_set('session.use_only_cookies', 1) == FALSE) {
		header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}
	// Gets current cookies params.
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"],
		$cookieParams["path"],
		$cookieParams["domain"],
		$secure,
		$httponly);
	// Sets the session name to the one set above.
	session_name($session_name);
    */
	session_start();	// Start the PHP session
	//session_regenerate_id();	// Regenerated the session, delete the old one.
}

function changePassword($user, $userId, $newPwd, $mysqli) {
    $success = false;
    $errorMsg = '';

    if ($user == "patient") {
        // Create a random SALT
        $randomSalt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

        // Create salted password
        $password = hash('sha512', $newPwd . $randomSalt);

        $prepStmtChangePwd = "UPDATE patients SET password = ?, salt = ? WHERE patient_id = ?";
        $stmtChangePwd = $mysqli->prepare($prepStmtChangePwd);
        if ($stmtChangePwd) {
            $stmtChangePwd->bind_param('ssi', $password, $randomSalt, $userId);
            $stmtChangePwd->execute();
            $stmtChangePwd->close();

            $success = true;
            $errorMsg = '';
            return json_encode(array("success" => $success, "errorMsg" => $errorMsg));
        } else {
            $success = false;
            $errorMsg = "Error preparing SQL statement.";
            return json_encode(array("success" => $success, "errorMsg" => $errorMsg));
        }
    } else if ($user == "physician") {
        // Create a random SALT
        $randomSalt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

        // Create salted password
        $password = hash('sha512', $newPwd . $randomSalt);

        $prepStmtChangePwd = "UPDATE physicians SET password = ?, salt = ? WHERE physician_id = ?";
        $stmtChangePwd = $mysqli->prepare($prepStmtChangePwd);
        if ($stmtChangePwd) {
            $stmtChangePwd->bind_param('ssi', $password, $randomSalt, $userId);
            $stmtChangePwd->execute();
            $stmtChangePwd->close();

            $success = true;
            $errorMsg = '';
            return json_encode(array("success" => $success, "errorMsg" => $errorMsg));
        } else {
            $success = false;
            $errorMsg = "Error preparing SQL statement.";
            return json_encode(array("success" => $success, "errorMsg" => $errorMsg));
        }
    }

    $success = false;
    $errorMsg = "An unknown error has occurred.";
    return json_encode(array("success" => $success, "errorMsg" => $errorMsg));
}

function checkPassword($user, $userId, $pwd, $mysqli) {
    $success = false;
    $pwdCorrect = false;
    $errorMsg = '';

    if ($user == "patient") {
        $prepStmtCheckPwd = "SELECT password, salt FROM patients WHERE patient_id = ?";
        $stmtCheckPwd = $mysqli->prepare($prepStmtCheckPwd);
        $dbPwd = '';
        $salt = '';
        if ($stmtCheckPwd) {
            $stmtCheckPwd->bind_param('i', $userId);
            $stmtCheckPwd->execute();
            $stmtCheckPwd->store_result();
            $stmtCheckPwd->bind_result($dbPwd, $salt);
            $stmtCheckPwd->fetch();
            $stmtCheckPwd->close();

            $pwd = hash('sha512', $pwd . $salt);

            if ($dbPwd == $pwd) {
                $success = true;
                $pwdCorrect = true;
                $errorMsg = "Error preparing SQL statement.";
                return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
            } else {
                $success = true;
                $pwdCorrect = false;
                $errorMsg = "Error preparing SQL statement.";
                return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
            }
        } else {
            $success = false;
            $pwdCorrect = false;
            $errorMsg = "Error preparing SQL statement.";
            return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
        }
    } else if ($user == "physician") {
        $prepStmtCheckPwd = "SELECT password, salt FROM physicians WHERE physician_id = ?";
        $stmtCheckPwd = $mysqli->prepare($prepStmtCheckPwd);
        $dbPwd = '';
        $salt = '';
        if ($stmtCheckPwd) {
            $stmtCheckPwd->bind_param('i', $userId);
            $stmtCheckPwd->execute();
            $stmtCheckPwd->store_result();
            $stmtCheckPwd->bind_result($dbPwd, $salt);
            $stmtCheckPwd->fetch();
            $stmtCheckPwd->close();

            $pwd = hash('sha512', $pwd . $salt);

            if ($dbPwd == $pwd) {
                $success = true;
                $pwdCorrect = true;
                $errorMsg = "Error preparing SQL statement.";
                return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
            } else {
                $success = true;
                $pwdCorrect = false;
                $errorMsg = "Error preparing SQL statement.";
                return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
            }
        } else {
            $success = false;
            $pwdCorrect = false;
            $errorMsg = "Error preparing SQL statement.";
            return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
        }
    }

    $success = false;
    $pwdCorrect = false;
    $errorMsg = "An unknown error has occurred.";
    return json_encode(array("success" => $success, "errorMsg" => $errorMsg, "pwdCorrect" => $pwdCorrect));
}

function login($email, $password, $mysqli) {
    $authed = false;
    $errorCode = -1;
    $line = -1;

    // Using prepared statements means that SQL injection is not possible.
    $prep_stmt_physician = "SELECT physician_id, username, password, salt, first_name, last_name FROM physicians WHERE email = ? LIMIT 1";
    $prep_stmt_patient = "SELECT patient_id, username, password, salt, first_name, last_name FROM patients WHERE email = ? LIMIT 1";
    $stmtPhys = $mysqli->prepare($prep_stmt_physician);
    $stmtPatient = $mysqli->prepare($prep_stmt_patient);
	$user_id = -1;
	$username = '';
	$db_password = '';
	$salt = '';
	$first_name = '';
	$last_name = '';
	$isPatient = true;
    if ($stmtPatient && $stmtPhys) {
        $stmtPatient->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmtPatient->execute();    // Execute the prepared query.
        $stmtPatient->store_result();
 
        // get variables from result.
        $stmtPatient->bind_result($user_id, $username, $db_password, $salt, $first_name, $last_name);
        $stmtPatient->fetch();

		if ($stmtPatient->num_rows < 1) {
			$stmtPhys->bind_param('s', $email);
			$stmtPhys->execute();
			$stmtPhys->store_result();
			
			$stmtPhys->bind_result($user_id, $username, $db_password, $salt, $first_name, $last_name);
			$stmtPhys->fetch();

			if ($stmtPhys->num_rows < 1) {
				// User does not exist
                $authed = false;
                $errorCode = 4002;
                $line = __LINE__;
				return array("authed" => $authed, "errorCode" => $errorCode, "line" => $line);
			} else if ($stmtPhys->num_rows > 1) {
				// Should never happen
                $authed = false;
                $errorCode = 4004;
                $line = __LINE__;
                return array("authed" => $authed, "errorCode" => $errorCode, "line" => $line);
			}
			$isPatient = false;
		} else if ($stmtPatient->num_rows > 1) {
			// Should never happen.
            $authed = false;
            $errorCode = 4004;
            $line = __LINE__;
            return array("authed" => $authed, "errorCode" => $errorCode, "line" => $line);
		}
	}

        error_log("LOGIN :: email { $email }, password { $password }");
 
	// hash the password with the unique salt.
	$password = hash('sha512', $password . $salt);

        error_log("LOGIN :: salt { $salt }");
        error_log("LOGIN :: salted password { $password }");

	// If the user exists we check if the account is locked
	// from too many login attempts 

	/*if (checkbrute($user_id, $mysqli) == true) {
		// Account is locked 
		// Send an email to user saying their account is locked
		return false;
	} else {*/
    // Check if the password in the database matches
    // the password the user submitted.
 
    if ($db_password == $password) {
        // Password is correct!
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
        // XSS protection as we might print this value
        $user_id = preg_replace("/[^0-9]+/", '', $user_id);
        $_SESSION['user_id'] = $user_id;
        // XSS protection as we might print this value
        $username = preg_replace("/[^a-zA-Z0-9_\-]+/", '', $username);
        $_SESSION['username'] = $username;
        $_SESSION['login_string'] = hash('sha512', $password . $user_browser);
        $_SESSION['is_patient'] = $isPatient;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['user_id'] = $user_id;
        if ($isPatient) {
            $physicianIds = [];
            $singleId = -1;
            $prepStmtGetPatientPhys = "SELECT physician_id FROM patient_physicians WHERE id = ?";
            $stmtGetPatientPhys = $mysqli->prepare($prepStmtGetPatientPhys);
            if ($stmtGetPatientPhys) {
                $stmtGetPatientPhys->bind_param('i', $user_id);
                $stmtGetPatientPhys->execute();
                $stmtGetPatientPhys->bind_result($singleId);
                while ($stmtGetPatientPhys->fetch()) {
                    array_push($physicianIds, $singleId);
                }
                $stmtGetPatientPhys->close();
            }
            $_SESSION['phys_ids'] = $physicianIds;
        }
        // Login successful.
        $authed = true;
        return array("authed" => $authed, "errorCode" => $errorCode, "line" => $line);
    }
    $authed = false;
    $errorCode = 4003;
    $line = __LINE__;
    error_log("Login: `db_password`: " . $db_password);
    return array("authed" => $authed, "errorCode" => $errorCode, "line" => $line);
    /* else {
        // Password is not correct
        // We record this attempt in the database
        $now = time();
        $mysqli->query("INSERT INTO login_attempts(user_id, time) VALUES ('$user_id', '$now')");
        return false;
    }*/
    //}
}

function checkbrute($user_id, $mysqli) {
	// Get timestamp of current time
	$now = time();

	// All login attempts are counted from the past 2 hours.
	$valid_attempts = $now - (2 * 60 * 60);

	if ($stmt = $mysqli->prepare("SELECT time FROM login_attempts WHERE user_id = ? AND time > '$valid_attempts'")) {
		$stmt->bind_param('i', $user_id);

		// Execute the prepared query.
		$stmt->execute();
		$stmt->store_result();

		// If there have been more than 5 failed logins
		if ($stmt->num_rows > 5) {
			return true;
		} else {
			return false;
		}
	}
}

function login_check($mysqli) {
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'], $_SESSION['is_patient'], $_SESSION['first_name'], $_SESSION['last_name'])) {
 
        $user_id = $_SESSION['user_id'];
		//error_log(print_r($user_id, TRUE));
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['username'];
		$isPatient = $_SESSION['is_patient'];
		$firstName = $_SESSION['first_name'];
		$lastName = $_SESSION['last_name'];
		
		$prepStmtPatient = "SELECT password FROM patients WHERE patient_id = ? LIMIT 1";
		$prepStmtPhys = "SELECT password FROM physicians WHERE physician_id = ? LIMIT 1";
 
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
 
        if ($stmt = $mysqli->prepare($isPatient ? $prepStmtPatient : $prepStmtPhys)) {
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();
 
            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);
 
                if ($login_check == $login_string) {
                    // Logged In!!!! 
                    return true;
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Not logged in 
            return false;
        }
    } else {
        // Not logged in 
        return false;
    }
}

function esc_url($url) {
	if ('' == $url) {
		return $url;
	}

	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
	
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$url = (string) $url;

	$count = 1;
	while ($count) {
		$url = str_replace($strip, '', $url, $count);
	}

	$url = str_replace(';//', '://', $url);
	
	$url = htmlentities($url);

	$url = str_replace('&amp;', '&#038;', $url);
	$url = str_replace("'", '&#039;', $url);

	if ($url[0] !== '/') {
		// We're only interested in relative links from $_SERVER['PHP_SELF']
		return '';
	} else {
		return $url;
	}
}
function autoVer($url){
    error_log('::::::::::: url'.$url);
    if($url === '/js/exam_audio_view.js' || $url === '/js/exam_main.js' || $url === '/js/exam_video_view.js' || $url === '/js/patient_main.js' || $url === '/js/patient_view.js' || $url === '/js/physician_main.js' || $url === '/js/physician_settings.js' || $url === '/js/register_patients.js' || $url === '/js/register_physician.js' || $url === '/js/single_waiting_room.js' || $url === '/js/single_waiting_room_worker.js' || $url === '/js/waiting_room.js' || $url === '/style/exam_audio_view.css' || $url === '/style/exam_main.css' || $url === '/style/exam_video_view.css' || $url === '/style/patient_main.css' || $url === '/style/patient_view.css' || $url === '/style/physician_main.css' || $url === '/style/physician_settings.css' || $url === '/style/register_patient.css' || $url === '/style/register_physician.css' || $url === '/style/single_waiting_room.css' || $url === '/style/video_chat.css' || $url === '/style/waiting_room.css' || $url === '/style/sweetalert.css' || $url === '/style/physician_header_link.css'){
        $path = pathinfo($url);
        $ver = '.'.filemtime($_SERVER['DOCUMENT_ROOT'].$url).'.';
        return $path['dirname'].'/'.str_replace('.', $ver, $path['basename']);
    }else{
        return $url;
    }
}
function autoVerCss($url){
    error_log('::::::::::: url'.$url);
    $path = pathinfo($url);
    $ver = '.'.filemtime($_SERVER['DOCUMENT_ROOT'].$url).'.';
    echo $path['dirname'].'/'.str_replace('.', $ver, $path['basename']);
}
/**
 * This function will attempt to format the input into
 * a phone number of the format (000) 000-0000 x00000
 * @param $phone string Raw string representing a phone number
 * @return string A formatted phone number string, or NULL if
 * you don't pass it a string/the number can't be formatted.
 */
function getFormattedPhone($phone) {
    if (gettype($phone) !== "string")
        return NULL;
    $length = strlen($phone);

    // This case should really never happen, but just in case
    // 7 digits are treated as a phone number w/o an area code
    // Ex. 000-0000
    if ($length === 7) {
        $prefix = substr($phone, 0, 3);
        $lineNum = substr($phone, 3);
        return ($prefix . "-" . $lineNum);
    } else if ($length === 10) {
        // Standard 10-digit phone number, with area code
        // Ex. (000) 000-0000
        $areaCode = substr($phone, 0, 3);
        $prefix = substr($phone, 3, 3);
        $lineNum = substr($phone, 6);
        return ("(" . $areaCode . ") " . $prefix . "-" . $lineNum);
    } else if ($length > 10) {
        // The first 10 digits are treated as the standard
        // 10-digit phone number while the remaining digits
        // are treated as the extension (up to 5 digits)
        $areaCode = substr($phone, 0, 3);
        $prefix = substr($phone, 3, 3);
        $lineNum = substr($phone, 6, 4);
        $ext = substr($phone, 10);
        return ("(" . $areaCode . ") " . $prefix . "-" . $lineNum . " x" . $ext);
    }
    return NULL;
}

/**
 * This function takes in a DateTime object representing a
 * birthday, and returns the age of the person in years.
 * @param $bday DateTime object representing a birthday
 * @return string Returns a string containing the number
 * of years since the birthday from now.
 */
function getAge($bday) {
    $today = new DateTime("now", new DateTimeZone('UTC'));
    $interval = $today->diff($bday);

    return $interval->format('%y');
}

class ExtendedPhysicianInfo {
    public $physicianId = -1;
    public $npi = '';
    public $username = '';
    public $email = '';
    public $firstName = '';
    public $middleName = '';
    public $lastName = '';
    public $gender = '';
    public $dob = NULL;
    public $phone = '';
    public $practiceName = '';
    public $practiceAddr = '';
    public $practiceCity = '';
    public $practiceState = '';
    public $practiceZip = '';
    public $homeAddr = '';
    public $homeCity = '';
    public $homeState = '';
    public $homeZip = '';
    public $email_notification ='';

    function __construct($physId, $npi, $username, $email, $fname, $mname, $lname, $gender, $dob, $phone, $practiceName,
                         $practiceAddr, $practiceCity, $practiceState, $practiceZip, $homeAddr,
                         $homeCity, $homeState, $homeZip, $email_notification) {
        $this->physicianId = $physId;
        $this->npi = $npi;
        $this->username = $username;
        $this->email = $email;
        $this->firstName = $fname;
        $this->middleName = $mname;
        $this->lastName = $lname;
        $this->gender = $gender;
        $this->dob = $dob;
        $this->phone = $phone;
        $this->practiceName = $practiceName;
        $this->practiceAddr = $practiceAddr;
        $this->practiceCity = $practiceCity;
        $this->practiceState = $practiceState;
        $this->practiceZip = $practiceZip;
        $this->homeAddr = $homeAddr;
        $this->homeCity = $homeCity;
        $this->homeState = $homeState;
        $this->homeZip = $homeZip;
        $this->email_notification = $email_notification;
    }
}

class PatientInfo {
	public $firstName = '';
    public $middleName = '';
	public $lastName = '';
	public $patientId = -1;
	public $username = '';
	public $gender = '';
	public $dob = NULL;
    public $mrn = -1;
    public $phone = '';
    public $phoneType = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $zip = '';
    public $waitingroom = '';
    public $uploaded = '';

	function __construct($uname, $id, $fname, $mname, $lname, $gen, $db, $mrn, $ph, $phTp, $addr, $city, $st, $zip, $waitingroom, $uploaded) {
		$this->firstName = $fname;
        $this->middleName = $mname;
		$this->lastName = $lname;
		$this->patientId = $id;
		$this->username = $uname;
		$this->gender = $gen;
		$this->dob = $db;
        $this->mrn = $mrn;
        $this->phone = $ph;
        $this->phoneType = $phTp;
        $this->address = $addr;
        $this->city = $city;
        $this->state = $st;
        $this->zip = $zip;
        $this->waitingroom = $waitingroom;
        $this->uploaded = $uploaded;
	}
}

class ExtendedPatientInfo {
	public $name = '';
	public $patientId = -1;
	public $username = '';
	public $email = '';
	public $mrn = '';
	public $gender = '';
    public $phoneType = '';
	public $phone = '';
	public $dob = NULL;
	public $address = '';
	public $city = '';
	public $state = '';
	public $zip = -1;
    public $physician = '';

	function __construct($uname, $id, $name, $eml, $medrn, $gen, $phType, $ph, $db, $addr, $cty, $st, $zp, $physician) {
		$this->name = $name;
		$this->patientId = $id;
		$this->username = $uname;
		$this->mrn = $medrn;
		$this->email = $eml;
		$this->gender = $gen;
        $this->phoneType = $phType;
		$this->phone = $ph;
		$this->dob = $db;
		$this->address = $addr;
		$this->city = $cty;
		$this->state = $st;
		$this->zip = $zp;
        $this->physician = $physician;
	}
}

class PatientDemographics {
    public $patientId = -1;
    public $homePhone = '';
    public $cellPhone = '';
    public $workPhone = '';
    public $emergencyContactName = '';
    public $emergencyContactPhone = '';
    public $insuraceCarrier = '';
    public $insurancePolicyId = '';
    public $insuranceGroupId = '';
    public $insuranceClaimAddr = '';

    function __construct($id, $hPhone, $cPhone, $wPhone, $eContactName, $eContactPh, $insCarrier, $insPolicyId, $insGrpId, $insClaimAddr) {
        $this->patientId = $id;
        $this->homePhone = $hPhone;
        $this->cellPhone = $cPhone;
        $this->workPhone = $wPhone;
        $this->emergencyContactName = $eContactName;
        $this->emergencyContactPhone = $eContactPh;
        $this->insuraceCarrier = $insCarrier;
        $this->insurancePolicyId = $insPolicyId;
        $this->insuranceGroupId = $insGrpId;
        $this->insuranceClaimAddr = $insClaimAddr;
    }
}

class PhysicianInfo {
	public $firstName = '';
    public $middleName = '';
	public $lastName = '';
	public $physicianId = -1;
	public $username = '';

	function __construct($uname, $id, $fname, $mname, $lname) {
		$this->firstName = $fname;
        $this->middleName = $mname;
		$this->lastName = $lname;
		$this->physicianId = $id;
		$this->username = $uname;
	}
}

class ExamInfo {
	public $examId = -1;
	public $examDate = '';
	public $examParts = '';
	public $patientId = -1;
	public $physicianId = -1;
	public $incline = -1;
	public $posture = '';
	public $temperature = -1;
	public $bpSystolic = -1;
    public $bpDiastolic = -1;
	public $pulse = -1;
	public $respRate = -1;
	public $pusleOximetry = -1;
	public $painLevel = -1;
	public $notes = '';
	
	function __construct($eId, $eDate, $eParts, $patId, $physId, $incl, $pos, $temp, $bpS, $bpD, $pls, $resp, $plsOx, $pain, $nt) {
		$this->examId = $eId;
		$this->examDate = $eDate;
		$this->examParts = $eParts;
		$this->patientId = $patId;
		$this->physicianId = $physId;
		$this->incline = $incl;
		$this->posture = $pos;
		$this->temperature = $temp;
		$this->bpSystolic = $bpS;
        $this->bpDiastolic = $bpD;
		$this->pulse = $pls;
		$this->respRate = $resp;
		$this->pulseOximetry = $plsOx;
		$this->painLevel = $pain;
		$this->notes = $nt;
	}
}

class HistoryItem {
    public $id = -1;
    public $abbrev = '';
    public $description = '';
    public $patientResponse = '';

    function __construct($hId, $abb, $desc, $resp) {
        $this->id = $hId;
        $this->abbrev = $abb;
        $this->description = $desc;
        $this->patientResponse = $resp;
    }
}

class SymptomItem {
    public $id = -1;
    public $abbrev = '';
    public $description = '';
    public $groupName = '';
    public $groupId = -1;

    function __construct($sId, $abb, $descr, $gName, $gId) {
        $this->id = $sId;
        $this->abbrev = $abb;
        $this->description = $descr;
        $this->groupName = $gName;
        $this->groupId = $gId;
    }
}

class HospitalItem {
    public $id = -1;
    public $name = '';

    function __construct($sId, $n) {
        $this->id = $sId;
        $this->name = $n;
    }
}

class ExamComponent {
    public $id = -1;
    public $title = '';
    public $type = '';
    public $abbrev = '';
    public $desc = '';
    public $time = '';

    function __construct($id, $title, $type, $abbrev, $desc, $time) {
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->abbrev = $abbrev;
        $this->desc = $desc;
        $this->time = strtotime($time);
    }
}

class SelectedExamComponent {
    public $id = -1;
    public $title = '';
    public $type = '';
    public $abbrev = '';
    public $desc = '';
    public $selected = false;
    public $sort = -1;
    public $public = -1;
    public $author_physician = -1;
    public $updated_at = null;

    /**
     * SelectedExamComponent constructor.
     * @param ExamComponent $ec
     * @param boolean $sel
     */
    function __construct($ec, $sel) {
        $this->id = $ec->id;
        $this->title = $ec->title;
        $this->type = $ec->type;
        $this->abbrev = $ec->abbrev;
        $this->desc = (isset($ec->description)?$ec->description:$ec->desc);
        $this->selected = (isset($ec->selected)?$ec->selected:$sel);
        $this->sort = $ec->sort;
        $this->public = $ec->public;
        $this->author_physician = $ec->author_physician;   
        $this->updated_at =(isset($ec->updated_at)?$ec->updated_at:'');
    }
}

class SelectedExamDocument {
    public $id = -1;
    public $filename = '';
    public $name_document = '';
    /**
     * SelectedExamDocument constructor.
     * @param ExamDocument $ed
     * @param boolean $sel
     */
    function __construct($ed, $sel) {
        $this->id = $ed->id;
        $this->filename = $ed->filename;
        $this->name_document = $ed->name_document;
    }
}

/**
 * The purpose of this function is to take in an array of patient IDs
 * and return an array of @PatientInfo objects for each one, thus
 * consolidating the number of SQL calls we have to make to the DB.
 * @param $mysqli mysqli object
 * @param $patientIds @array of patient ids
 * @return array of @PatientInfo objects, or NULL if you don't give it an
 * array as input, the SQL query fails, etc.
 */
function getPatientInfosFromArray($mysqli, $patientIds /*Array*/) {
    if (!is_array($patientIds)) {
        return NULL;
    } else if (count($patientIds) === 0) {
        return array();
    }

    $returnVals = array();
    $paramTypes = '';
    $num = count($patientIds);
    $prepStmtGetPatientInfos = "SELECT patient_id, username, first_name, middle_name, last_name, gender, dob FROM patients WHERE";
    for ($i = 0; $i < $num; $i++) {
        $paramTypes .= "i";
        if ($i === ($num - 1))
            $prepStmtGetPatientInfos .= " patient_id = ?";
        else
            $prepStmtGetPatientInfos .= " patient_id = ? OR";
    }
    $stmtGetPatientInfos = $mysqli->prepare($prepStmtGetPatientInfos);
    if ($stmtGetPatientInfos) {
        $id = -1;
        $username = '';
        $firstName = '';
        $middleName = '';
        $lastName = '';
        $gender = '';
        $dob = '';
        call_user_func_array(array(&$stmtGetPatientInfos, 'bind_param'), array_merge(array($paramTypes), refValues(array_values($patientIds))));
        $stmtGetPatientInfos->execute();
        $stmtGetPatientInfos->bind_result($id, $username, $firstName, $middleName, $lastName, $gender, $dob);
        while ($stmtGetPatientInfos->fetch()) {
            $dob = DateTime::createFromFormat("Y-m-d", $dob, new DateTimeZone("UTC"));
            $idx = array_search($id, $patientIds);
            $returnVals[$idx] = new PatientInfo($username, $id, $firstName, $middleName, $lastName, $gender, $dob);
        }
        $stmtGetPatientInfos->close();
    } else {
        return NULL;
    }

    return $returnVals;
}

function refValues($arr) {
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $refs = array();
        foreach ($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

function removePatientsNotToDisplay(&$patientList, $physId, $mysqli) {
    if ($patientList == null)
        return;
    $num = count($patientList);

    for ($i = 0; $i < $num; $i++) {
        $patientId = $patientList[$i]->patientId;
        $prepStmtCheckDispPatient = "SELECT * FROM patients_no_display WHERE patient_id = ? AND phys_id = ?";
        $stmtCheckDispPatient = $mysqli->prepare($prepStmtCheckDispPatient);

        if ($stmtCheckDispPatient) {
            $stmtCheckDispPatient->bind_param('ii', $patientId, $physId);
            $stmtCheckDispPatient->execute();
            $stmtCheckDispPatient->bind_result($tmp1, $tmp2);
            $stmtCheckDispPatient->store_result();
            $stmtCheckDispPatient->fetch();

            if ($stmtCheckDispPatient->num_rows > 0)
                unset($patientList[$i]);

            $stmtCheckDispPatient->close();
        }
    }
}

/**
 * Funcion que obtiene el listado de pacientes de un doctor ya eliminando los que no quiere que se 
 * desplieguen
 *
 * @param Integer $id        Id del doctor
 * @param Object  $mysqli    Coneccion de mysql 
 * @param Integer $numOffSet El primer indice que tomara 
 * @param Integer $numLimit  Total de elementos a tomar
 *
 * @return array el listado de pacientes, si no manda limite se mandaran todos
 **/
function getPatientsOfPhysicianAndRemoveNotDisplay($id, $mysqli, $numOffSet = 0, $numLimit = 0) {
    $response = [];
    $strQuery = 'SELECT pat.patient_id, pat.username, pat.first_name, pat.middle_name, pat.last_name, pat.gender, pat.dob, ' .
                'pat.mrn, pat.phone, pat.phone_type, pat.address, pat.city, pat.state, pat.zip, war.entered_at, war.uploaded ' .
                'FROM patient_physicians pah ' .
                'LEFT JOIN patients pat ON pah.id = pat.patient_id  ' .
                'LEFT JOIN waiting_room war ON pah.id = war.patient_id '.
                'LEFT JOIN patients_no_display pnd ON pnd.patient_id = pat.patient_id AND pnd.phys_id = pah.physician_id ' .
                'WHERE pah.physician_id = ' . $id . ' AND pnd.patient_id IS NULL ' .
                'ORDER BY war.entered_at DESC ';

    if( $numLimit > 0 ){
        $strQuery .=  ' LIMIT ' . $numLimit . ' OFFSET ' . $numOffSet;
    }
    error_log( __METHOD__ . ' :: $strQuery :: ' . $strQuery  );
    $result   = $mysqli->query($strQuery);
    if (is_object($result) && property_exists($result, 'num_rows') && $result->num_rows > 0) {
        $response = array_map(
            function($patient) {
                extract($patient); //
                //error_log( __METHOD__ . ' :: $strQuery :: ' . $waitingroom  );
                $dob = DateTime::createFromFormat('Y-m-d', $dob, new DateTimeZone('UTC'));
                return new PatientInfo( $username, $patient_id, $first_name, $middle_name, 
                                       $last_name, $gender, $dob, $mrn, $phone, $phone_type, 
                                       $address, $city, $state, $zip, $entered_at, $uploaded);
            }, $result->fetch_all(MYSQLI_ASSOC)
        );

    }
    //error_log( __METHOD__ . ' :: $strQuery :: ' . $response  );
    return $response;
}

// This function get the patients associated with the provided physician id
function getPatientsOfPhysician($id, $mysqli) {
	/*
        $patientIds = [];
	$patientInfos = [];
	$singleId = -1;
	$prepStmtGetPatients = "SELECT id FROM patient_physicians WHERE physician_id = ?";
	$prepStmtGetPatientInfo = "SELECT patient_id, username, first_name, middle_name, last_name, gender, dob, mrn, phone, phone_type, address, city, state, zip FROM patients WHERE patient_id = ?";
	$stmtGetPatients = $mysqli->prepare($prepStmtGetPatients);
	$stmtGetPatientInfo = $mysqli->prepare($prepStmtGetPatientInfo);
	
	if ($stmtGetPatients) {
		$stmtGetPatients->bind_param('i', $id);
		$stmtGetPatients->execute();
		$stmtGetPatients->bind_result($singleId);
		while ($stmtGetPatients->fetch()) {
			array_push($patientIds, $singleId);
		}
		$stmtGetPatients->close();
		$indexCount = count($patientIds);
		if ($stmtGetPatientInfo) {
			for ($index = 0; $index < $indexCount; $index++) {
				$id = -1;
				$username = '';
				$firstName = '';
                $middleName = '';
				$lastName = '';
				$gender = '';
				$dob = '';
                $mrn = '';
                $phone = '';
                $phoneType = '';
                $address = '';
                $city = '';
                $state = '';
                $zip = '';
				$stmtGetPatientInfo->bind_param('i', $patientIds[$index]);
				$stmtGetPatientInfo->execute();
				$stmtGetPatientInfo->bind_result($id, $username, $firstName, $middleName, $lastName, $gender, $dob, $mrn, $phone, $phoneType, $address, $city, $state, $zip);
				$stmtGetPatientInfo->fetch();
                $dob = DateTime::createFromFormat("Y-m-d", $dob, new DateTimeZone("UTC"));
				array_push($patientInfos, new PatientInfo($username, $id, $firstName, $middleName, $lastName, $gender, $dob, $mrn, $phone, $phoneType, $address, $city, $state, $zip));
			}
			return $patientInfos;
		}
	}
	return NULL;
        */
        $response = [];
    $result = $mysqli->query("
        SELECT patient_id, username, first_name, middle_name, last_name, gender, dob, mrn, phone, phone_type, address, city, state, zip 
        FROM patient_physicians 
        JOIN patients ON patient_id = id 
        WHERE physician_id = $id 
        ORDER BY id DESC
    ");
    if (is_object($result) && property_exists($result, 'num_rows') && $result->num_rows > 0) {
        $response = array_map(
            function($patient) {
                extract($patient);
                $dob = DateTime::createFromFormat('Y-m-d', $dob, new DateTimeZone('UTC'));
                return new PatientInfo($username, $patient_id, $first_name, $middle_name, $last_name, $gender, $dob, $mrn, $phone, $phone_type, $address, $city, $state, $zip);
            }, $result->fetch_all(MYSQLI_ASSOC)
        );

    }
    return $response;
}

function getPhysiciansOfPatient($id, $mysqli) {
	$physicianIds = [];
	$physicianInfos = [];
	$singleId = -1;
	$prepStmtGetPhysicians = "SELECT physician_id FROM patient_physicians WHERE id = ?";
	$prepStmtGetPhysicianInfo = "SELECT physician_id, username, first_name, middle_name, last_name FROM physicians WHERE physician_id = ?";
	$stmtGetPhysicians = $mysqli->prepare($prepStmtGetPhysicians);
	$stmtGetPhysicianInfo = $mysqli->prepare($prepStmtGetPhysicianInfo);

	if ($stmtGetPhysicians) {
		$stmtGetPhysicians->bind_param('i', $id);
		$stmtGetPhysicians->execute();
		$stmtGetPhysicians->bind_result($singleId);
		while ($stmtGetPhysicians->fetch()) {
			array_push($physicianIds, $singleId);
		}
		$stmtGetPhysicians->close();
		$indexCount = count($physicianIds);
		if ($stmtGetPhysicianInfo) {
			for ($index = 0; $index < $indexCount; $index++) {
				$id = -1;
				$username = '';
				$firstName = '';
                $middleName = '';
				$lastName = '';
				$stmtGetPhysicianInfo->bind_param('i', $physicianIds[$index]);
				$stmtGetPhysicianInfo->execute();
				$stmtGetPhysicianInfo->bind_result($id, $username, $firstName, $middleName, $lastName);
				$stmtGetPhysicianInfo->fetch();
				array_push($physicianInfos, new PhysicianInfo($username, $id, $firstName, $middleName, $lastName));
			}
			return $physicianInfos;
		}
	}
	return NULL;
}

// This function checks to see if the physician has permission to view the patient's files
function physicianHasPermission($physId, $patientId, $mysqli) {
	$prepStmtGetPatient = "SELECT physician_id FROM patient_physicians WHERE id = ?";
	$stmtGetPatient = $mysqli->prepare($prepStmtGetPatient);
	$physIdFromDb = -1;
	if ($stmtGetPatient) {
		$stmtGetPatient->bind_param('i', $patientId);
		$stmtGetPatient->execute();
		$stmtGetPatient->bind_result($physIdFromDb);
		while ($stmtGetPatient->fetch()) {
			// Patient is registered with this physician
			if ($physIdFromDb == $physId)
				return true;
		}
	}
	return false;
}

function getPatientInfo($patientId, $mysqli) {
	$prepStmtGetPatientInfo = "SELECT patient_id, username, first_name, middle_name, last_name, gender, dob FROM patients WHERE patient_id = ?";
	$stmtGetPatientInfo = $mysqli->prepare($prepStmtGetPatientInfo);
	if ($stmtGetPatientInfo) {
		$id = -1;
		$username = '';
		$firstName = '';
        $middleName = '';
		$lastName = '';
        $gender = '';
        $dob = '';
		$stmtGetPatientInfo->bind_param('i', $patientId);
		$stmtGetPatientInfo->execute();
		$stmtGetPatientInfo->bind_result($id, $username, $firstName, $middleName, $lastName, $gender, $dob);
		$stmtGetPatientInfo->fetch();
        $dob = DateTime::createFromFormat("Y-m-d", $dob);
		return new PatientInfo($username, $id, $firstName, $middleName, $lastName, $gender, $dob);
	} else {
		return NULL;
	}
}

function getExtendedPatientInfo($patientId, $mysqli) {
	$prepStmtGetPatientInfoEx = "
        SELECT patient_id, patients.username, CONCAT(patients.first_name, IF('' <> patients.middle_name, CONCAT(' ', patients.middle_name), ''),' ', patients.last_name) AS name, patients.email, mrn, patients.gender, phone_type, patients.phone, patients.dob, address, city, state, zip, IFNULL(CONCAT(physicians.first_name , ' ', physicians.last_name), '') AS physicians_name 
        FROM patients 
        LEFT JOIN patient_physicians ON id = patient_id 
        LEFT JOIN physicians         ON physicians.physician_id = patient_physicians.physician_id
        WHERE patient_id = $patientId
    ";
	$stmtGetPatientInfoEx = $mysqli->prepare($prepStmtGetPatientInfoEx);
	if ($stmtGetPatientInfoEx) {
		$id        = -1;
		$username  = '';
		$name      = '';
		$email     = '';
		$mrn       = '';
		$gender    = '';
        $phoneType = '';
		$phone     = -1;
		$dob       = '';
		$address   = '';
		$city      = '';
		$state     = '';
		$zip       = -1;
        $physician = '';
		$stmtGetPatientInfoEx->execute();
		$stmtGetPatientInfoEx->bind_result($id, $username, $name, $email, $mrn, $gender, $phoneType, $phone, $dob, $address, $city, $state, $zip, $physician);
		$stmtGetPatientInfoEx->fetch();
        $dob = DateTime::createFromFormat('Y-m-d', $dob);
		return new ExtendedPatientInfo($username, $id, $name, $email, $mrn, $gender, $phoneType, $phone, $dob, $address, $city, $state, $zip, $physician);
	} else {
		return NULL;
	}
}

/**
 * This function fetches and returns a PatientDemographics object
 * representing the columns from the patient_demographics table
 *
 * @param int $patientId
 * @param mysqli $mysqli
 * @return PatientDemographics object
 */
function getPatientDemographics($patientId, $mysqli) {
    $prepStmtGetPatientDemographics = "SELECT patient_id, home_phone, cell_phone, work_phone, emergency_contact_name, emergency_contact_ph, ins_carrier, ins_policy_id, ins_group_id, ins_claim_addr FROM patient_demographics WHERE patient_id = ?";
    $stmtGetPatientDemographics = $mysqli->prepare($prepStmtGetPatientDemographics);
    if ($stmtGetPatientDemographics) {
        $pId = -1;
        $homePhone = '';
        $cellPhone = '';
        $workPhone = '';
        $eContactName = '';
        $eContactPh = '';
        $insCarrier = '';
        $insPolicyId = '';
        $insGroupId = '';
        $insClaimAddr = '';
        $stmtGetPatientDemographics->bind_param('i', $patientId);
        $stmtGetPatientDemographics->execute();
        $stmtGetPatientDemographics->store_result();

        if ($stmtGetPatientDemographics->num_rows === 0)
            return NULL;

        $stmtGetPatientDemographics->bind_result($pId, $homePhone, $cellPhone, $workPhone, $eContactName, $eContactPh, $insCarrier, $insPolicyId, $insGroupId, $insClaimAddr);
        $stmtGetPatientDemographics->fetch();
        $stmtGetPatientDemographics->free_result();
        $stmtGetPatientDemographics->close();
        return new PatientDemographics($pId, $homePhone, $cellPhone, $workPhone, $eContactName, $eContactPh, $insCarrier, $insPolicyId, $insGroupId, $insClaimAddr);
    } else {
        return NULL;
    }
}

function updateExtendedPatientInfo($extendedPatientInfo, $mysqli) {
	$prepStmtUpdatePatientInfoEx = "UPDATE patients SET email=?, mrn=?, gender=?, phone=?, dob=?, address=?, city=?, state=?, zip=? WHERE patient_id=?";
	$stmtUpdatePatientInfoEx = $mysqli->prepare($prepStmtUpdatePatientInfoEx);
	if ($stmtUpdatePatientInfoEx) {
		$stmtUpdatePatientInfoEx->bind_param('sssssssssi', $extendedPatientInfo->email, $extendedPatientInfo->mrn, $extendedPatientInfo->gender, $extendedPatientInfo->phone, $extendedPatientInfo->dob, $extendedPatientInfo->address, $extendedPatientInfo->city, $extendedPatientInfo->state, $extendedPatientInfo->zip, $extendedPatientInfo->patientId);
		$stmtUpdatePatientInfoEx->execute();
		
		if ($stmtUpdatePatientInfoEx->errno) {
			echo("Failed to update info. ".$stmtUpdatePatientInfoEx->error);
		} else {
			echo("Update {$stmtUpdatePatientInfoEx->affected_rows} rows");
		}
		$stmtUpdatePatientInfoEx->close();
	}
}

function getPhysicianInfo($physId, $mysqli) {
	$prepStmtGetPhysInfo = "SELECT physician_id, username, first_name, middle_name, last_name FROM physicians WHERE physician_id = ?";
	$prepStmtGetPhysInfo = $mysqli->prepare($prepStmtGetPhysInfo);
	if ($prepStmtGetPhysInfo) {
		$id = -1;
		$username = '';
		$firstName = '';
        $middleName = '';
		$lastName = '';
		$prepStmtGetPhysInfo->bind_param('i', $physId);
		$prepStmtGetPhysInfo->execute();
		$prepStmtGetPhysInfo->bind_result($id, $username, $firstName, $middleName, $lastName);
		$prepStmtGetPhysInfo->fetch();
		$prepStmtGetPhysInfo->close();
		return new PatientInfo($username, $id, $firstName, $middleName, $lastName, '', '');
	} else {
		return NULL;
	}
}

function dateCompDesc($a, $b) {
	if ($a->examDate == $b->examDate)
		return 0;
	return ($a->examDate < $b->examDate) ? 1 : -1;
}

function getPatientExams($patientId, $mysqli) {
	$examInfos = [];
	$prepStmt = "SELECT exam_id, exam_date, exam_parts, patient_id, physician_id, incline, posture, temperature, bp_systolic, bp_diastolic, pulse, resp_rate, pulse_oximetry, pain_level, notes FROM exams WHERE patient_id = ?";
	$stmt = $mysqli->prepare($prepStmt);
	if ($stmt) {
		$examId = -1;
		$examDate = '';
		$examParts = '';
		$pId = -1;
		$physicianId = -1;
		$incline = -1;
		$posture = '';
		$temperature = -1;
		$bpSystolic = -1;
        $bpDiastolic = -1;
		$pulse = -1;
		$respRate = -1;
		$pulseOx = -1;
		$painLevel = -1;
		$notes = '';
		$stmt->bind_param('i', $patientId);
		$stmt->execute();
		$stmt->bind_result($examId, $examDate, $examParts, $pId, $physicianId, $incline, $posture, $temperature, $bpSystolic, $bpDiastolic, $pulse, $respRate, $pulseOx, $painLevel, $notes);
		while ($stmt->fetch()) {
            $timestamp = strtotime($examDate);
            try {
                $examDate = new DateTime('@'.$timestamp, new DateTimeZone("UTC"));
            } catch (Exception $e) {
                echo $e->getMessage();
            }

			array_push($examInfos, new ExamInfo($examId, $examDate, $examParts, $pId, $physicianId, $incline, $posture, $temperature, $bpSystolic, $bpDiastolic, $pulse, $respRate, $pulseOx, $painLevel, $notes));
		}
		$stmt->close();
		// Now sort the 'ExamInfo' objects by Date (descending order)
		usort($examInfos, "dateCompDesc");
		return $examInfos;
	}
	return NULL;
}

/**
 * @param $patientId
 * @param $examId
 * @param $mysqli
 * @return ExamInfo object
 */
function getSingleExam($patientId, $examId, $mysqli) {
	$exams = getPatientExams($patientId, $mysqli);
	$exam = NULL;
	if ($exams != NULL) {
		for ($i = 0; $i < count($exams); $i++) {
			if ($exams[$i]->examId == $examId) {
				$exam = $exams[$i];
				break;
			}
		}
	}
	return $exam;
}

function getHospitalsList($mysqli) {
    $hospitals = [];
    // Select all hospitals except "Other"
    $prepStmtGetHospitals = "SELECT * FROM hospitals WHERE hospital_id != 1";
    $stmtGetHospitals = $mysqli->prepare($prepStmtGetHospitals);
    if ($stmtGetHospitals) {
        $hospitalId = -1;
        $hospitalName = '';
        $stmtGetHospitals->execute();
        $stmtGetHospitals->bind_result($hospitalId, $hospitalName);
        while ($stmtGetHospitals->fetch())
            array_push($hospitals, new HospitalItem($hospitalId, $hospitalName));
        $stmtGetHospitals->close();

        return $hospitals;
    }
    return NULL;
}

function getHospitalId($physId, $mysqli) {
    $prepStmtGetHospitalId = "SELECT assoc_hospital FROM physicians WHERE physician_id = ?";
    $stmtGetHospitalId = $mysqli->prepare($prepStmtGetHospitalId);
    if ($stmtGetHospitalId) {
        $hospitalId = -1;
        $stmtGetHospitalId->bind_param('i', $physId);
        $stmtGetHospitalId->execute();
        $stmtGetHospitalId->bind_result($hospitalId);
        $stmtGetHospitalId->fetch();
        $stmtGetHospitalId->close();

        return $hospitalId;
    }
    return -1;
}

function getExamParts($patientId, $examId, $mysqli) {
	$exams = getPatientExams($patientId, $mysqli);
	$len = count($exams);
	$exam = NULL;
	for ($i = 0; $i < $len; $i++) {
		if ($exams[$i]->examId == $examId) {
			$exam = $exams[$i];
			break;
		}
	}
	$examParts = json_decode($exam->examParts, true);
	return $examParts;
}

/**
 * Fetches and returns an array of HistoryItem classes representing
 * the complete set of history questions.
 *
 * @param $patientId int patient ID
 * @param $examId int exam ID
 * @param $mysqli mysqli mysqli object
 * @return array An array of HistoryItem objects
 */
function getHistoryForExam($patientId, $examId, $mysqli) {
    $historyItems = [];
    $prepStmtGetHistDescrs = "SELECT * FROM history";
    $stmtGetHistDescrs = $mysqli->prepare($prepStmtGetHistDescrs);
    if ($prepStmtGetHistDescrs) {
        $id = -1;
        $abbrev = '';
        $descr = '';
        $stmtGetHistDescrs->execute();
        $stmtGetHistDescrs->bind_result($id, $abbrev, $descr);
        while ($stmtGetHistDescrs->fetch()) {
            $historyItems[$id] = new HistoryItem($id, $abbrev, $descr, '');
        }
        $stmtGetHistDescrs->close();
    }
    $prepStmtGetExamHist = "SELECT * FROM patient_history WHERE patient_id = ? AND exam_id = ?";
    $stmtGetExamHist = $mysqli->prepare($prepStmtGetExamHist);
    if ($stmtGetExamHist) {
        $pId = -1;
        $eId = -1;
        $mainCond = '';
        $sympLoc = '';
        $sympQual = '';
        $sympSev = '';
        $sympLen = '';
        $sympTime = '';
        $sympBetter = '';
        $sympWorse = '';
        $sympProb = '';
        $pastMedCond = '';
        $pastSurg = '';
        $famHist = '';
        $doUSmoke = '';
        $smokePacks = '';
        $alcCons = '';
        $illicitDrugs = '';
        $allergies = '';
        $currentMeds = '';
        $stmtGetExamHist->bind_param('ii', $patientId, $examId);
        $stmtGetExamHist->execute();
        $stmtGetExamHist->bind_result($pId, $eId, $mainCond, $sympLoc, $sympQual, $sympSev, $sympLen, $sympTime,
            $sympBetter, $sympWorse, $sympProb, $pastMedCond, $pastSurg, $famHist, $doUSmoke, $smokePacks,
            $alcCons, $illicitDrugs, $allergies, $currentMeds);
        $stmtGetExamHist->fetch();
        $stmtGetExamHist->close();

        // Now add these to each class in the array
        $historyItems[0]->patientResponse = $mainCond;
        $historyItems[1]->patientResponse = $sympLoc;
        $historyItems[2]->patientResponse = $sympQual;
        $historyItems[3]->patientResponse = $sympSev;
        $historyItems[4]->patientResponse = $sympLen;
        $historyItems[5]->patientResponse = $sympTime;
        $historyItems[6]->patientResponse = $sympBetter;
        $historyItems[7]->patientResponse = $sympWorse;
        $historyItems[8]->patientResponse = $sympProb;
        $historyItems[9]->patientResponse = $pastMedCond;
        $historyItems[10]->patientResponse = $pastSurg;
        $historyItems[11]->patientResponse = $famHist;
        $historyItems[12]->patientResponse = $doUSmoke;
        $historyItems[13]->patientResponse = $smokePacks;
        $historyItems[14]->patientResponse = $alcCons;
        $historyItems[15]->patientResponse = $illicitDrugs;
        $historyItems[16]->patientResponse = $allergies;
        $historyItems[17]->patientResponse = $currentMeds;

        return $historyItems;
    }
    return NULL;
}

/**
 * Fetches and returns an array of SymptomItem classes representing
 * the symptoms the user checked in the app.
 *
 * @param $patientId int patient ID
 * @param $examId int Exam ID
 * @param $mysqli mysqli mysqli object
 * @return array of SymptomItem objects, or NULL if any part
 * of the MySQL fetch statement fails. The returned array may be
 * empty also. This indicates that the user did not check any
 * symptoms in the exam.
 */
function getSymptomsForExam($patientId, $examId, $mysqli) {
    $symptoms = [];
    $symptomsAssoc = [];
    $symptomsStr = '';
    $prepStmtGetSymptomsStr = "SELECT symptoms FROM exams WHERE exam_id = ?";
    $stmtGetSymptomsStr = $mysqli->prepare($prepStmtGetSymptomsStr);
    if ($stmtGetSymptomsStr) {
        $stmtGetSymptomsStr->bind_param('i', $examId);
        $stmtGetSymptomsStr->execute();
        $stmtGetSymptomsStr->bind_result($symptomsStr);
        $stmtGetSymptomsStr->fetch();
        $stmtGetSymptomsStr->close();

        $symptomsArr = json_decode($symptomsStr);

        if (is_null($symptomsArr))
            return NULL;

        for ($i = 0; $i < count($symptomsArr); $i++) {
            $prepStmtGetSymptomInfo = "SELECT * FROM symptoms WHERE abbrev = ?";
            $stmtGetSymptomInfo = $mysqli->prepare($prepStmtGetSymptomInfo);

            if ($stmtGetSymptomInfo) {
                $id = -1;
                $abbrev = '';
                $desc = '';
                $groupName = '';
                $groupId = -1;
                $stmtGetSymptomInfo->bind_param('s', $symptomsArr[$i]);
                $stmtGetSymptomInfo->execute();
                $stmtGetSymptomInfo->bind_result($id, $abbrev, $desc, $groupName, $groupId);
                $stmtGetSymptomInfo->fetch();
                $stmtGetSymptomInfo->close();

                array_push($symptoms, new SymptomItem($id, $abbrev, $desc, $groupName, $groupId));
            }
        }

        // Now create an associative array
        /*for ($i = 0; $i < count($symptoms); $i++) {
            if (!array_key_exists($symptomsAssoc[$i]->groupId, $symptomsAssoc))
                $symptomsAssoc[$symptoms[$i]->groupId] = array();
        }

        for ($i = 0; $i < count($symptoms); $i++) {
            // This is faster than array_push
            $symptomsAssoc[$symptoms[$i]->groupId][] = $symptoms[$i];
            //array_push($symptomsAssoc[$symptoms[$i]->groupId], $symptoms[$i]);
        }

        unset($symptoms);

        return $symptomsAssoc;*/
        return $symptoms;
    }
    return NULL;
}

/**
 * This function takes in a physician ID and determines whether that physician
 * requires a BAA or not. If a BAA is needed, true will be returned, else false
 * will be returned.
 *
 * @param $physId int Physician ID
 * @param $mysqli mysqli object
 * @return bool A bool representing whether the BAA is needed or not
 */
function isBAANeeded($physId, $mysqli) {
    $prepStmtBaaNeeded = "SELECT baa_needed FROM physicians WHERE physician_id = ?";
    $stmtGetBaaNeeded = $mysqli->prepare($prepStmtBaaNeeded);
    $baaNeeded = -1;
    if ($stmtGetBaaNeeded) {
        $stmtGetBaaNeeded->bind_param('i', $physId);
        $stmtGetBaaNeeded->execute();
        $stmtGetBaaNeeded->bind_result($baaNeeded);
        $stmtGetBaaNeeded->fetch();
        $stmtGetBaaNeeded->close();
    }
    return boolval($baaNeeded);
}

/**
 * @param $physId int Physician ID
 * @param $baaNeeded bool boolean value indicating whether or not a BAA is needed
 * @param $mysqli mysqli object
 */
function setBAANeeded($physId, $baaNeeded, $mysqli) {
    $prepStmtSetBAANeeded = "UPDATE physicians SET baa_needed = ? WHERE physician_id = ?";
    $stmtSetBAANeeded = $mysqli->prepare($prepStmtSetBAANeeded);
    $baaNeeded = $baaNeeded ? 1 : 0;
    if ($stmtSetBAANeeded) {
        $stmtSetBAANeeded->bind_param('ii', $baaNeeded, $physId);
        $stmtSetBAANeeded->execute();
        $stmtSetBAANeeded->close();
    }
}

/**
 * This function gets and returns an ExtendedPhysicianInfo object
 * representing all of the database fields for physician info.
 *
 * @param Int $physId
 * @param mysqli $mysqli
 * @return ExtendedPhysicianInfo object representing all of the
 * database fields for physician info.
 */
function getExtendedPhysicianInfo($physId, $mysqli) {
    $prepStmtGetPhysInfo = "SELECT physician_id, npi, username, email, first_name, middle_name, last_name, gender, dob, phone, practice_name, practice_addr, practice_city, practice_state, practice_zip, home_addr, home_city, home_state, home_zip, email_notification FROM physicians WHERE physician_id = ?";
    $stmtGetPhysInfo = $mysqli->prepare($prepStmtGetPhysInfo);
    if ($stmtGetPhysInfo) {
        $physicianId = -1;
        $npi = '';
        $username = '';        
        $email = '';
        $firstName = '';
        $middleName = '';
        $lastName = '';
        $gender = '';
        $dob = NULL;
        $phone = '';
        $practiceName = '';
        $practiceAddr = '';
        $practiceCity = '';
        $practiceState = '';
        $practiceZip = '';
        $homeAddr = '';
        $homeCity = '';
        $homeState = '';
        $homeZip = '';
        $email_notification ='';
        $stmtGetPhysInfo->bind_param('i', $physId);
        $stmtGetPhysInfo->execute();
        $stmtGetPhysInfo->bind_result($physicianId, $npi, $username, $email, $firstName, $middleName, $lastName, $gender, $dob, $phone, $practiceName,
            $practiceAddr, $practiceCity, $practiceState, $practiceZip, $homeAddr, $homeCity, $homeState, $homeZip, $email_notification);
        $stmtGetPhysInfo->fetch();
        $dob = DateTime::createFromFormat("Y-m-d", $dob);

        return new ExtendedPhysicianInfo($physId, $npi, $username, $email, $firstName, $middleName, $lastName, $gender, $dob, $phone, $practiceName,
            $practiceAddr, $practiceCity, $practiceState, $practiceZip, $homeAddr, $homeCity, $homeState, $homeZip, $email_notification);
    } else {
        return NULL;
    }
}

/**
 * This function gets and returns an array of ExamComponent objects
 * representing all of the available exam components available in the
 * system.
 *
 * @param mysqli $mysqli
 * @return array of ExamComponents
 */
function getExamComponents($mysqli) {
    $examComponents = [];
    $prepStmtGetExamComponents = "SELECT * FROM exam_components WHERE deleted_at IS NULL ORDER BY sort";
    $stmtGetExamComponents = $mysqli->prepare($prepStmtGetExamComponents);
    if ($stmtGetExamComponents) {
        $id = -1;
        $title = '';
        $type = '';
        $abbrev = '';
        $desc = '';
        $sort = '';
        $stmtGetExamComponents->execute();
        $stmtGetExamComponents->bind_result($id, $title, $type, $abbrev, $desc, $sort, $public, $author_physician, $strDate);
        while ($stmtGetExamComponents->fetch())
            $examComponents[$abbrev] = new ExamComponent($id, $title, $type, $abbrev, $desc, $strDate);
        $stmtGetExamComponents->close();
    }
    return $examComponents;
}

/**
 * This function gets and returns an ExamComponent object of the
 * exam component matching the id parameter
 *
 * @param int $id
 * @param mysqli $mysqli
 * @return ExamComponent
 */
function getExamComponentById($id, $mysqli) {
    return __getExamComponentByParam("id", $id, $mysqli);
}

/**
 * This function gets and returns an ExamComponent object of the
 * exam component matching the abbrev parameter
 *
 * @param string $abbrev
 * @param mysqli $mysqli
 * @return ExamComponent
 */
function getExamComponentByAbbrev($abbrev, $mysqli) {
    return __getExamComponentByParam("abbrev", $abbrev, $mysqli);
}

/**
 * This function should NOT be used outside of "functions.php". It
 * is in a sense, a private function.
 *
 * @param $paramType
 * @param $param
 * @param mysqli $mysqli
 * @return ExamComponent
 */
function __getExamComponentByParam($paramType, $param, $mysqli) {
    $prepStmtGetExamComponent = "SELECT id, title, type, abbrev, description, sort, public, author_physician, updated_at FROM exam_components WHERE " . $paramType . " = ?";
    $stmtGetExamComponent = $mysqli->prepare($prepStmtGetExamComponent);
    if ($stmtGetExamComponent) {
        $id = -1;
        $title = '';
        $type = '';
        $abbrev = '';
        $desc = '';
        $sort = 1;
        $stmtGetExamComponent->bind_param(($paramType === "id") ? 'i' : 's', $param);
        $stmtGetExamComponent->execute();
        $stmtGetExamComponent->bind_result($id, $title, $type, $abbrev, $desc, $sort, $public, $author_physician, $strDate);
        $stmtGetExamComponent->fetch();
        $stmtGetExamComponent->close();

        return new ExamComponent($id, $title, $type, $abbrev, $desc, $strDate);
    }
    return NULL;
}

/**
 * This function gets and returns ExamComponent objects for all
 * exam components in the db, noting which ones were selected by
 * the specified physician.
 *
 * @param int $physId
 * @param mysqli $mysqli
 * @return array SelectedExamComponent
 */
function getAllExamComponents($physId, $mysqli) {
    $sql = "CALL sp_select_exam_components($physId);";
    $selectedComponents = array();
    if ($result = $mysqli->query($sql)) {
        while($row =$result->fetch_object()){
            $selectedComponents[] = new SelectedExamComponent($row, false);
        }
        $result->close();
    }    
    return $selectedComponents;
}

/**
 * This function gets and returns ExamComponent objects for all
 * exam components in the db, noting which ones were selected by
 * the specified physician.
 *
 * @param int $physId
 * @param mysqli $mysqli
 * @return array SelectedExamComponent
 */
function getPhysicianSelectedExamComponents($physId, $mysqli) {
    $sql = "CALL sp_select_exam_components_author_physician($physId);";
    $selectedComponents = array();
    if ($result = $mysqli->query($sql)) {
        while($row =$result->fetch_object()){
            $selectedComponents[] = new SelectedExamComponent($row, false);
        }
        $result->close();
    }    
    return $selectedComponents;
}

function endsWith($haystack, $needle) {
    return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function getInfoForEmail($mysqli, $senderid = 0, $recipientids = 0, $ispatientsender = true) {
    $filteredrecipients = '';
    $response           = [];
    if(!is_numeric($senderid) || 0 >= $senderid) {
        throw new Exception("Invalid sender id { $senderid }", 1);
    }
    if(is_array($recipientids) && 0 < count($recipientids)) {
        $filteredrecipients = implode(',', array_filter($recipientids, function($id) { return is_numeric($id) && 0 < $id; }));
    }
    else {
        $filteredrecipients = $recipientids;
    }
    if(!is_numeric($filteredrecipients) || '' == $filteredrecipients || 0 >= $filteredrecipients) {
        throw new Exception("Invalid recipients id { $recipientids }", 2);
    }
    $sendertable    = $ispatientsender ? 'patients'     : 'physicians';
    $sendercol      = $ispatientsender ? 'patient_id'   : 'physician_id';
    $recipienttable = $ispatientsender ? 'physicians'   : 'patients';
    $recipientcol   = $ispatientsender ? 'physician_id' : 'patient_id';
    error_log(":::::::::::SELECT CONCAT(first_name, ' ', IF('' <> middle_name, CONCAT(' ', middle_name), ''), last_name) AS name, email 
        FROM $sendertable 
        WHERE $sendercol = $senderid;

        SELECT CONCAT(first_name, ' ', IF('' <> middle_name, CONCAT(' ', middle_name), ''), last_name) AS name, email, email_notification 
        FROM $recipienttable 
        WHERE $recipientcol IN ($filteredrecipients);");
    if ($mysqli->multi_query("
        SELECT CONCAT(first_name, ' ', IF('' <> middle_name, CONCAT(' ', middle_name), ''), last_name) AS name, email 
        FROM $sendertable 
        WHERE $sendercol = $senderid;

        SELECT CONCAT(first_name, ' ', IF('' <> middle_name, CONCAT(' ', middle_name), ''), last_name) AS name, email, email_notification 
        FROM $recipienttable 
        WHERE $recipientcol IN ($filteredrecipients);
    ")) {
        do {
            if ($res = $mysqli->store_result()) {
                $response[] = $res->fetch_all(MYSQLI_ASSOC);
                $res->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    }
    return $response;
}
function checkInWaitingRoom($mysqli, $physicianid, $patient) {
    $patient['name'] = addslashes($patient['name']);
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    $sql = "INSERT IGNORE INTO waiting_room (physician_id, patient_id, patient_name, uploaded, entered_at) VALUES ($physicianid, {$patient['id']}, '{$patient['name']}', '{$patient['uploaded']}', UNIX_TIMESTAMP());";
    if (true == $mysqli->query($sql)) {
        $response['data'] = $patient;
        error_log(__METHOD__ . ':: OK: waiting_room');
    }
    else {
        $response['success']  = false;
        $response['errorMsg'] = 'You cannot been added to the waiting room, please, try again.';
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return json_encode($response);
}
function getPatientsFromWaitingRoom($mysqli, $physicianid) {
    $response = [];
    $result = $mysqli->query("
        SELECT 
        war.patient_id AS id, war.patient_name AS name, IFNULL(pat.gender, '') AS gender, 
        IFNULL(pat.last_name, '') AS lastName, IFNULL(pat.dob, '') AS dob, IFNULL(pat.mrn, '') AS mrn, IFNULL(pat.phone, '') AS phone,
        IFNULL(pat.phone_type, '') AS phone_type, IFNULL(pat.address, '') AS address, 
        IFNULL(pat.city, '') AS city, IFNULL(pat.state,'') AS state, IFNULL( pat.zip, '') AS zip, war.uploaded
        FROM waiting_room war
        LEFT JOIN patients pat ON pat.patient_id = war.patient_id
        WHERE war.physician_id = $physicianid
    ");
    if (is_object($result) && property_exists($result, 'num_rows') && $result->num_rows > 0) {
        $response = array_map(
            function($patient) {
                extract($patient);
                error_log($dob);
                error_log($name.' - '.$lastName); //teodoro - gonzales
                $oDate = DateTime::createFromFormat('Y-m-d', $dob, new DateTimeZone('UTC'));
                if( !($oDate === false) ){
                    $dob = $oDate->format('m/d/Y');
                } 
                return array(
                        'id'         => $id,
                        'name'       => $name,
                        'lastName'   => $lastName,
                        'gender'     => ucfirst($gender),
                        'dob'        => $dob,
                        'mrn'        => $mrn,
                        'phone'      => ($phone!=''?( ucfirst($phone_type). ' ' . 
                                        getFormattedPhone($phone)):''),
                        'address'    => ($address!=''?($address . ", " . $city . ", " . $state . 
                                        " " . $zip):''),
                        'uploaded'   => $uploaded,  
                       );
            }, $result->fetch_all(MYSQLI_ASSOC)
        );
    }
    //error_log('message :: getPatientsFromWaitingRoom:: response :: '.$response);
    return $response;
}
function isInWaitingRoom($mysqli, $patientid) {
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    $sql = "
        SELECT GROUP_CONCAT(CONCAT(first_name, ' ', last_name) SEPARATOR ', ') AS physicians 
        FROM waiting_room 
        JOIN physicians ON physicians.physician_id = waiting_room.physician_id
        WHERE patient_id = $patientid
        GROUP BY patient_id;
    ";
    if ($result = $mysqli->query($sql)) {
        $physicians = '';
        while ($row = $result->fetch_assoc()) {
            $physicians .= $row['physicians'];
        }
        $response['data'] = $physicians;
    }
    else {
        $response['success']  = false;
        $response['errorMsg'] = 'You could not been removed from the waiting room, please, try again.';
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return json_encode($response);
}
function leaveWaitingRoom($mysqli, $physicianid, $patientid) {
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    $sql = "
        INSERT IGNORE INTO waiting_room_history (physician_id, patient_id, patient_name, entered_at, leaved_at)
        SELECT physician_id, patient_id, patient_name, entered_at, UNIX_TIMESTAMP()
        FROM waiting_room 
        WHERE physician_id = $physicianid AND patient_id = $patientid;

        DELETE 
        FROM waiting_room 
        WHERE physician_id = $physicianid AND patient_id = $patientid;
    ";
    if (false == $mysqli->multi_query($sql)) {
        $response['success']  = false;
        $response['errorMsg'] = 'You could not been removed from the waiting room, please, try again.';
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return json_encode($response);
}

function getPhysicianInfoByUsername($mysqli, $username) {
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    $sql = "
        SELECT physician_id AS id, CONCAT('Dr. ', first_name, ' ', last_name) AS name 
        FROM physicians 
        WHERE username = '$username'
    ";
    if ($result = $mysqli->query($sql)) {
        $response['data'] = $result->fetch_assoc();
    }
    else {
        $response['success']  = false;
        $response['errorMsg'] = "Physician with username { $username } not found.";
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return $response;
}
function hashPassword($pwd) {
    // Create a random SALT
    $randomSalt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
    // Create salted password
    $password = hash('sha512', $pwd . $randomSalt);
    error_log("hashPassword :: randomSalt { $randomSalt }");
    error_log("hashPassword :: salted password { $password }");
    //Return both
    return ['randomSalt' => $randomSalt, 'pwd' => $password];
}

function getMaxStethRecordTime($mysqli, $id) {
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    $sql = "SELECT max_steth_record_time FROM physician_prefs WHERE id = $id";
    if ($result = $mysqli->query($sql)) {
        $response['data'] = $result->fetch_assoc();
    }
    else {
        $response['success']  = false;
        $response['errorMsg'] = "Max stethoscopy time for physician { $id } not found.";
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return $response;
}

function getPatientExam($mysqli, $patientid, $physicianid) {
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    $sql = "SELECT * FROM exams WHERE patient_id = $patientid AND  physician_id = $physicianid";
    if ($result = $mysqli->query($sql)) {
        while ($exam = $result->fetch_assoc()) {
            $response['data'][] = $exam;
        }
    }
    else {
        $response['success']  = false;
        $response['errorMsg'] = "Exam for patient { $patientid } and physician { $physicianid } not found.";
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return $response;
}

function generateToken($mysqli, $id) {
    $response = [
        'success'  => true,
        'errorMsg' => ''
    ];
    try {
        if(empty($id)) {
            throw new Exception('ID not specified as query string parameter.', 400);
        }
        $userCount = 0;
        $stmtCheckUserExists = $mysqli->prepare('SELECT COUNT(patient_id) FROM patients WHERE patient_id = ?');
        if ($stmtCheckUserExists) {
            $stmtCheckUserExists->bind_param('i', $id);
            $stmtCheckUserExists->execute();
            $stmtCheckUserExists->bind_result($userCount);
            $stmtCheckUserExists->fetch();
            $stmtCheckUserExists->close();
        } else {
            throw new Exception('Error preparing MySQL statement', 500);
        }
        // Check physicians
        if (0 == $userCount) {
            $stmtCheckUserExists = $mysqli->prepare('SELECT COUNT(physician_id) FROM physicians WHERE physician_id = ?');
            if ($stmtCheckUserExists) {
                $stmtCheckUserExists->bind_param('i', $id);
                $stmtCheckUserExists->execute();
                $stmtCheckUserExists->bind_result($userCount);
                $stmtCheckUserExists->fetch();
                $stmtCheckUserExists->close();
            } else {
                throw new Exception('Error preparing MySQL statement', 500);
            }
        }
        // Generate a unique token, and put it in the DB
        if ($userCount > 0) {
            $token = sha1(uniqid((string)$userCount, true));
            $stmtInsertToken = $mysqli->prepare('INSERT INTO pending_users (user_id, token, tstamp) VALUES (?, ?, ?)');
            if ($stmtInsertToken) {
                $stmtInsertToken->bind_param('isi', $id, $token, $_SERVER['REQUEST_TIME']);
                if ($stmtInsertToken->execute()) {
                    $stmtInsertToken->close();
                    $response['token'] = $token;
                }
            } else {
                throw new Exception('Error preparing MySQL statement', 500);
            }
        } else {
            throw new Exception('User ID does not exist in database.', 400);
        }
    }
    catch(Exception $e) {
        $response['success']  = false;
        $response['errorMsg'] = $e->getMessage();
        error_log(__METHOD__ . ':: Error: ' . $e->getMessage());
    }
    return $response;
}

function get_is_browser($user_agent)
{
    if (stristr($user_agent, 'Opera')  || stristr($user_agent, 'OPR/')   || 
        stristr($user_agent, 'Edge')   || stristr($user_agent, 'Chrome') || 
        stristr($user_agent, 'Safari') || stristr($user_agent, 'Firefox')|| 
        stristr($user_agent, 'MSIE')   || stristr($user_agent, 'Trident/7')) 
            return true; 
}

/**
 * This function gets and returns ExamDocuments objects by 
 * exam in the db, noting which ones were selected by
 * the specified physician.
 *
 * @param int $examId
 * @param mysqli $mysqli
 * @return array SelectedExamDocument
 */
function getExamDocuments($examId, $mysqli) {
    $sql = "CALL sp_select_exam_documents($examId);";
    $selectedDocuments = array();
    if ($result = $mysqli->query($sql)) {
        while($row =$result->fetch_object()){
            $selectedDocuments[] = new SelectedExamDocument($row, false);
        }
        $result->close();
    }    
    return $selectedDocuments;
}
function getPhysicianInfoWR($patientId, $mysqli) {
   $response = ['success'  => true,'errorMsg' => ''];
    $sql = "CALL sp_select_physician_info_wr($patientId);";
    if ($result = $mysqli->query($sql)) {
        while ($wrinfo = $result->fetch_assoc()) {
            $response['data'] = $wrinfo;
        }
    }
    else {
        $response['success']  = false;
        $response['errorMsg'] = "Information for waitingroom and physician { $physicianid } not found.";
        error_log(__METHOD__ . ':: Error: ' . $mysqli->error);
    }
    return $response;
}