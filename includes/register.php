<?php
include_once 'db_connect.php';
include_once 'functions.php';
//require_once '../util/swiftmailer/lib/swift_required.php';

error_log('***** INCLUDES :: REGISTER :: POST { ' . print_r($_POST, true) . ' } *****');

$referer = $_SERVER['HTTP_REFERER'];
$offset = strrpos($referer, '/');
$pageName = substr($referer, $offset + 1);

if (strcmp($pageName, "register_patient.php") === 0) {
    $haveMname = $_POST['mname'] !== "";
    $haveMRN = $_POST['mrn'] !== "";
    $haveEmail = $_POST['email'] !== "";
    $havePhone = $_POST['phone'] !== "";
    $haveAddr = $_POST['address'] !== "";
    $haveCity = $_POST['city'] !== "";
    $haveState = $_POST['state'] !== "SEL";
    $haveZip = $_POST['zip'] !== "";
    $havePwd = isset($_POST['pwdHashed']);
    $havePhysId = $_POST['physId'] !== "";
    $haveInsCompany = $_POST['insuranceCompany'] !== "";
    $haveInsAddr = $_POST['insuranceAddr'] !== "";
    $haveInsPhone = $_POST['insurancePhone'] !== "";
    $haveInsPH = $_POST['insurancePH'] !== "";
    $haveInsRelationship = $_POST['insPatientRelationship'] !== "SEL";
    $haveInsRelationshipOther = $_POST['insPatientRelationshipOther'] !== "";
    $haveInsGroup = $_POST['insGroupNum'] !== "";
    $haveInsID = $_POST['insIDCertNum'] !== "";
    $haveInsIssueDate = $_POST['insIssueDate'] !== "";

    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $mrn = $_POST['mrn'];
    $dob = $_POST['dob'];
    $dobFormatted = (new DateTime($dob, new DateTimeZone("UTC")))->format('Y-m-d');
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $physId = $_POST['physId'];
    $pwd = isset($_POST['pwdHashed'])?$_POST['pwdHashed']:'';
    $insCompany = $_POST['insuranceCompany'];
    $insAddr = $_POST['insuranceAddr'];
    $insPhone = $_POST['insurancePhone'];
    $insPH = $_POST['insurancePH'];
    $insRelationship = $_POST['insPatientRelationship'];
    $insRelationshipOther = $_POST['insPatientRelationshipOther'];
    $insGroup = $_POST['insGroupNum'];
    $insID = $_POST['insIDCertNum'];
    if ($haveInsIssueDate) {
        $insIssueDate = $_POST['insIssueDate'];
        $insIssueDateFormatted = new DateTime($insIssueDate, new DateTimeZone("UTC"));
        $insIssueDateFormatted = $insIssueDateFormatted->format('Y-m-d');
    }

    $validDatedob=false;
    //Validate date of birth format mm/dd/yyyy
    if (!empty($_POST['dob']) && preg_match ("/^(0\d|1[0-2])([-\\/])(0[1-9]|1[0-9]|3[01])([-\\/])((?:19|20)\d{2})$/",$_POST['dob'],$res_match)) {
            $validDatedob=true;
    }

    // At least these fields need to be present, if not send to error page.
    if (!empty($_POST['fname'])  && !empty($_POST['lname']) && !empty($_POST['dob']) && !empty($_POST['gender']) && 
        !empty($_POST['physId']) && ctype_digit($_POST['physId']) && (int)$_POST['physId'] > 0 && $validDatedob) 

    {
        if ($havePwd && strlen($_POST['pwdHashed']) !== 128) {
            header('Location: ../error.php?error=1001&l='.__LINE__);
            exit();
        }

        if ($haveEmail) {
            // Check to see if email already exists in DB of both patients AND physicians
            $prepStmtCheckEmail = "SELECT patient_id FROM patients WHERE email = ?";
            $stmtCheckEmail = $mysqli->prepare($prepStmtCheckEmail);
            if ($stmtCheckEmail) {
                $stmtCheckEmail->bind_param('s', $email);
                $stmtCheckEmail->execute();
                $stmtCheckEmail->store_result();
                if ($stmtCheckEmail->num_rows == 1) {
                    $stmtCheckEmail->close();
                    header('Location: ../error.php?error=1002&l='.__LINE__);
                    exit();
                }
                $stmtCheckEmail->close();
            } else {
                header('Location: ../error.php?error=2000&l='.__LINE__);
                exit();
            }
        }

        // Now, check to make sure that the physician ID was not tampered with,
        // and is a real physician
        if ($havePhysId) {
            $prepStmtCheckPhys = "SELECT physician_id FROM physicians WHERE physician_id = ?";
            $stmtCheckPhys = $mysqli->prepare($prepStmtCheckPhys);
            if ($stmtCheckPhys) {
                $stmtCheckPhys->bind_param('i', $physId);
                $stmtCheckPhys->execute();
                $stmtCheckPhys->store_result();
                if ($stmtCheckPhys->num_rows < 1) {
                    $stmtCheckPhys->close();
                    header('Location: ../error.php?error=1003&l='.__LINE__);
                    exit();
                }
                $stmtCheckPhys->close();
            } else {
                header('Location: ../error.php?error=2000&l='.__LINE__);
                exit();
            }
        }

        if ($havePwd) {
            $arrPass = hashPassword($pwd);
        }

        $userId = 0;
        /*
        // Generate a random number 8-digit number between: 10^7 and (10^8) - 1, or 10000000-99999999.
        // With an 8-digit number, there will be 89,999,999 unique possibilities
        // Also, by using an 8-digit number, we can ensure that it fits into a standard INT column in MySql.
        // This means there is a 1:89999999 chance of getting the same number
        $digits = 8;
        $num = 0;
        // Force while statement to execute once
        $userCount = 1;
        while ($userCount > 0) {
            $num = mt_rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            $prepStmtCheckId = "SELECT COUNT(*) FROM patients WHERE patient_id = ?";
            $stmtCheckId = $mysqli->prepare($prepStmtCheckId);
            if ($stmtCheckId) {
                $stmtCheckId->bind_param('i', $num);
                $stmtCheckId->execute();
                $stmtCheckId->bind_result($userCount);
                $stmtCheckId->fetch();
                $stmtCheckId->close();
            } else {
                header('Location: ../error.php?error=2000&l='.__LINE__);
                exit();
            }
        }
        $userId = $num;
        */
        /*
        if ($haveEmail)
            $username = explode('@', $email)[0];
        */

        // Insert the new user into the database
        $valuesToInsert = [];
        //$valuesToInsert['patient_id'] = $userId;
        $valuesToInsert['first_name'] = $fname;
        if ($haveMname)
            $valuesToInsert['middle_name'] = $mname;
        $valuesToInsert['last_name'] = $lname;
        if (isset($username))
            $valuesToInsert['username'] = $username;
        if ($haveEmail)
            $valuesToInsert['email'] = $email;
        if ($havePwd) {
            $valuesToInsert['password'] = $arrPass['pwd'];
            $valuesToInsert['salt'] = $arrPass['randomSalt'];
        }
        if ($haveMRN)
            $valuesToInsert['mrn'] = $mrn;
        $valuesToInsert['gender'] = $gender;
        $valuesToInsert['dob'] = $dobFormatted;
        if ($havePhone)
            $valuesToInsert['phone'] = $phone;
        if ($haveAddr)
            $valuesToInsert['address'] = $addr;
        if ($haveCity)
            $valuesToInsert['city'] = $city;
        if ($haveState)
            $valuesToInsert['state'] = $state;
        if ($haveZip)
            $valuesToInsert['zip'] = $zip;
        if ($haveInsCompany)
            $valuesToInsert['insurance_company'] = $insCompany;
        if ($haveInsAddr)
            $valuesToInsert['insurance_address'] = $insAddr;
        if ($haveInsPhone)
            $valuesToInsert['insurance_phone'] = $insPhone;
        if ($haveInsPH)
            $valuesToInsert['insurance_ph_name'] = $insPH;
        if ($haveInsRelationship) {
            if ($insRelationship === "other")
                $valuesToInsert['insurance_patient_relationship'] = $insRelationshipOther;
            else
                $valuesToInsert['insurance_patient_relationship'] = $insRelationship;
        }
        if ($haveInsGroup)
            $valuesToInsert['insurance_group_num'] = $insGroup;
        if ($haveInsID)
            $valuesToInsert['insurance_id_cert_num'] = $insID;
        if ($haveInsIssueDate)
            $valuesToInsert['insurance_issue_date'] = $insIssueDateFormatted;

        $prepStmtRegister = 'INSERT INTO patients(' . implode(',', array_keys($valuesToInsert)) . ') VALUES("' . implode('","', array_values($valuesToInsert)) .  '")';
        /*
        $i = 0;
        $numVals = count($valuesToInsert);
        $lastIndex = $numVals - 1;
        foreach ($valuesToInsert as $key => $val) {
            if ($i < $lastIndex)
                $prepStmtRegister .= ($key . ', ');
            else
                $prepStmtRegister .= ($key . ')');
            $i++;
        }
        $prepStmtRegister .= ' VALUES(';
        for ($i = 0; $i < $numVals; $i++) {
            if ($i < $lastIndex)
                $prepStmtRegister .= '?, ';
            else
                $prepStmtRegister .= '?)';
        }

        $paramTypesStr = 'i';
        $paramTypesStr .= str_repeat('s', $numVals - 1);
        */

        /*$prepStmtInsert = "INSERT INTO patients (patient_id, first_name, middle_name, last_name, email, dob, gender"
            . ($haveMRN ? ", mrn" : "") . ($havePhone ? ", phone" : "") . ($haveAddr ? ", address" : "")
            . ($haveCity ? ", city" : "") . ($haveState ? ", state" : "") . ($haveZip ? ", zip" : "")
            . ($havePwd ? ", password, salt" : "") . ")" . " VALUES (?, ?, ?, ?, ?, ?, ?" . ($haveMRN ? ", ?" : "")
            . ($havePhone ? ", ?" : "") . ($haveAddr ? ", ?" : "") . ($haveCity ? ", ?" : "") . ($haveState ? ", ?" : "")
            . ($haveZip ? ", ?" : "") . ($havePwd ? ", ?, ?" : "") . ")";
        $params = [];
        array_push($params, $userId, $fname, $mname, $lname, $email, $dobFormatted, $gender);
        if ($haveMRN)
            $params[] = $mrn;
        if ($havePhone)
            $params[] = $phone;
        if ($haveAddr)
            $params[] = $addr;
        if ($haveCity)
            $params[] = $city;
        if ($haveState)
            $params[] = $state;
        if ($haveZip)
            $params[] = $zip;
        if ($havePwd) {
            $params[] = $password;
            $params[] = $randomSalt;
        }*/
        $stmtRegister = $mysqli->prepare($prepStmtRegister);
        if ($stmtRegister) {
            /*
            $funcParams = array_values($valuesToInsert);
            array_unshift($funcParams, $paramTypesStr);
            call_user_func_array(array($stmtRegister, "bind_param"), refValues($funcParams));
            */
            //$paramTypes = "issssss" . ($haveMRN ? "s" : "") . ($havePhone ? $funcParams"s;" : "") . ($haveAddr ? "s" : "") . ($haveCity ? "s" : "") . ($haveState ? "s" : "") . ($haveZip ? "s" : "") . ($havePwd ? "ss" : "");
            /*
            call_user_func_array(array(&$stmtInsert, 'bind_param'), array_merge(array($paramTypes), refValues(array_values($params))));*/
            if (!$stmtRegister->execute()) {
                //error_log("MySQL statement execution failure: {$stmtInsert->error}");
                error_log("MySQL statement execution failure: {$mysqli->error}");
                $stmtRegister->close();
                //header('Location: ../error.php?error=2001&l='.__LINE__);
                exit();
            }
            $userId = $mysqli->insert_id;
            $stmtRegister->close();
        } else {
            header('Location: ../error.php?error=2000&l='.__LINE__);
            //error_log("MySQL statement preparation failure: " . $prepStmtInsert);
            error_log("MySQL statement preparation failure: " . $mysqli->error);
            exit();
        }

        // Now, create the link between the physician and patient
        if ($havePhysId && intval($physId) > 0) {
            $prepStmtCreateLink = "INSERT INTO patient_physicians (id, physician_id) VALUES (?, ?)";
            $stmtCreateLink = $mysqli->prepare($prepStmtCreateLink);
            if ($stmtCreateLink) {
                $stmtCreateLink->bind_param('ii', $userId, $physId);
                if (!$stmtCreateLink->execute()) {
                    $stmtCreateLink->close();
                    header('Location: ../error.php?error=2001&l='.__LINE__);
                    exit();
                }
                $stmtCreateLink->close();
            } else {
                header('Location: ../error.php?error=2000&l='.__LINE__);
                exit();
            }
        }

        // Now create the patient's upload folder
        if (file_exists(UPLOADS_LOCATION . $physId)) {
            if (!mkdir(UPLOADS_LOCATION . $physId . "/" . $userId)) {
                header('Location: ../error.php?error=1005&l='.__LINE__);
                exit();
            }
        } else {
            error_log("Directory: " . (UPLOADS_LOCATION . $physId));
            header('Location: ../error.php?error=1004&l='.__LINE__);
            exit();
        }
    } else {
        header('Location: ../error.php?error=1000&l='.__LINE__);
        exit();
    }

    //sendRegCompleteEmail($email, $fname, $lname);

    header('Location: ../register_success.php');
} else {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    if ($dob !== "") {
        $dobFormatted = new DateTime($dob, new DateTimeZone("UTC"));
        $dobFormatted = $dobFormatted->format('Y-m-d');
    }
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $hosp = isset($_POST['hospital'])?$_POST['hospital']:'';
    $npi = isset($_POST['npi'])?$_POST['npi']:'';
    $pwd = $_POST['pwdHashed'];

    // Physician Registration
    if (isset($_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['email'], $_POST['dob'], $_POST['gender'], $_POST['npi'], $_POST['hospital'])) {
        // Check to see if email already exists in DB of both patients AND physicians
        $prepStmtCheckEmail = "SELECT physician_id FROM physicians WHERE email = ?";
        $stmtCheckEmail = $mysqli->prepare($prepStmtCheckEmail);
        if ($stmtCheckEmail) {
            $stmtCheckEmail->bind_param('s', $email);
            $stmtCheckEmail->execute();
            $stmtCheckEmail->store_result();
            if ($stmtCheckEmail->num_rows == 1) {
                $stmtCheckEmail->close();
                header('Location: ../error.php?error=1002&l='.__LINE__);
                exit();
            }
            $stmtCheckEmail->close();
        } else {
            header('Location: ../error.php?error=2000&l='.__LINE__);
            exit();
        }

        $data = getNPIVerification($npi);
        if (is_null($data)) {
            header('Location: ../error.php?error=3002&l='.__LINE__);
            exit();
        } else if (($data->errorNum !== -1) && ($data->line !== -1)) {
            header('Location: ../error.php?error=' . $data->errorNum . "&l=" . $data->line);
            exit();
        } else {
            // NPI is valid, but it may already be in the database
            if (isNPIInDB($npi, $mysqli)) {
                // Send to error page, NPI already exists in DB
                header('Location: ../error.php?error=1006&l='.__LINE__);
                exit();
            } else {
                // Proceed with registration
                if (strlen($_POST["pwdHashed"]) !== 128) {
                    header('Location: ../error.php?error=1001&l='.__LINE__);
                    exit();
                } else {
                    $arrPass = hashPassword($pwd);
                    // Generate a random number 8-digit number between: 10^7 and (10^8) - 1, or 10000000-99999999.
                    // With an 8-digit number, there will be 89,999,999 unique possibilities
                    // Also, by using an 8-digit number, we can ensure that it fits into a standard INT column in MySql.
                    // This means there is a 1/89999999 chance of getting the same number
                    $userId = 0;
                    /*
                    $digits = 8;
                    $num = 0;
                    // Force while statement to execute once
                    $userCount = 1;

                    while ($userCount > 0) {
                        $num = mt_rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                        $prepStmtCheckId = "SELECT COUNT(*) FROM physicians WHERE physician_id = ?";
                        $stmtCheckId = $mysqli->prepare($prepStmtCheckId);
                        if ($stmtCheckId) {
                            $stmtCheckId->bind_param('i', $num);
                            $stmtCheckId->execute();
                            $stmtCheckId->bind_result($userCount);
                            $stmtCheckId->fetch();
                            $stmtCheckId->close();
                        } else {
                            header('Location: ../error.php?error=2000&l='.__LINE__);
                            exit();
                        }
                    }
                    $userId = $num;
                    */
                    //Generate username for waiting room
                    $username = '';
                    $yob      = isset($dob)?explode('/', $dob)[2]:time();
                    do {
                        switch ($username) {
                            case '':
                                $username = "dr$lname";
                                break;
                            case "dr$lname":
                                $username = "dr$fname$lname";
                                break;
                            case "dr$fname$lname":
                                $username = "dr$fname$lname$yob";
                                break;
                            default:
                                $username = "dr$fname$lname" . time();
                                break;
                        }
                        $result = $mysqli->query("SELECT physician_id FROM physicians WHERE username = '$username'");
                    } while (0 < $result->num_rows);

                    $username = strtolower($username);

                    // Insert the new user into the database
                    $prepStmtInsert = 'INSERT INTO physicians (npi, username, email, password, salt, first_name, middle_name, last_name, gender, dob, phone, practice_addr, practice_city, practice_state, practice_zip, assoc_hospital) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                    $stmtInsert = $mysqli->prepare($prepStmtInsert);
                    if ($stmtInsert) {
                        $stmtInsert->bind_param('issssssssssssssi', $npi, $username, $email, $arrPass['pwd'], $arrPass['randomSalt'], $fname, $mname, $lname, $gender, $dob, $phone, $addr, $city, $state, $zip, $hosp);
                        if (!$stmtInsert->execute()) {
                            error_log("MySQL statement execution failure: {$stmtInsert->error}");
                            error_log("MySQL statement execution failure: {$mysqli->error}");
                            $stmtInsert->close();
                            header('Location: ../error.php?error=2001&l='.__LINE__);
                            exit();
                        }
                        $userId = $mysqli->insert_id;
                        $stmtInsert->close();

                        // Hay que insertar al doctor los componentes defaults
                        try{
                           $strQuery = 'INSERT INTO physician_prefs( id, normal, phone_home, ' .
                                       'phone_work, phone_cell, exam_components, ' . 
                                       'max_steth_record_time) VALUES( ?, ?, ?, ?, ?, ?, ? );';
                           $oStmt    = $mysqli->prepare($strQuery);
                           $strDef   = '';
                           $numDef   = 0;
                           $strComp  = '["htt","mm","aas","aps","ats","ams","ala","alm","arm",' .
                                       '"rjva","ljva","rleek","lleek","mv1"]';
                           if ($oStmt) {
                               $oStmt->bind_param('isssssi', $userId, $strDef, $strDef, $strDef, $strDef, $strComp, $numDef);
                               
                               if (!$oStmt->execute()) {
                                   error_log( __METHOD__ . ':: Cant create a physician_prefs ::' ) ;
                               }
			       $oStmt->close();
                           }else{
                               error_log( __FILE__ . ':: NO EXISTE EL OSTMT  :: ' );
                           }
                        }catch( Exception $e ){
                            error_log( __METHOD__ . ':: Exception ::' . $e->getMessage()) ;
                        }
                        // Hay que insertar al doctor los componentes defaults

                    } else {
                        header('Location: ../error.php?error=2000&l='.__LINE__);
                        error_log("MySQL statement preparation failure: " . $prepStmtInsert);
                        error_log("MySQL statement preparation failure: " . $mysqli->error);
                        exit();
                    }

                    // Now create the physician's upload folder
                    if (!mkdir(UPLOADS_LOCATION . $userId)) {
                        header('Location: ../error.php?error=1005&l='.__LINE__);
                        exit();
                    }
                }
            }
        }
    } else {
        header('Location: ../error.php?error=1000&l='.__LINE__);
        exit();
    }

    header('Location: ../register_success.php');
}

function getNPIVerification($npi) {
    $url = "https://vpexam.com/includes/verify_npi.php?npi=" . $npi;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (!$content = curl_exec($ch)) {
        curl_close($ch);
        header('Location: ../error.php?error=3001&l='.__LINE__);
        exit();
    }
    curl_close($ch);

    return json_decode($content);
}

function isNPIInDB($npi,  $mysqli) {
    $num = -1;
    $prepStmtCheckNPI = "SELECT COUNT(*) FROM physicians WHERE npi = ?";
    $stmtCheckNPI = $mysqli->prepare($prepStmtCheckNPI);
    if ($stmtCheckNPI) {
        $stmtCheckNPI->bind_param('s', $npi);
        $stmtCheckNPI->execute();
        $stmtCheckNPI->bind_result($num);
        $stmtCheckNPI->fetch();
        $stmtCheckNPI->close();
    } else {
        header('Location: ../error.php?error=2000&l='.__LINE__);
        exit();
    }

    return $num > 0;
}

/*function sendRegCompleteEmail($email, $fname, $lname) {
    $body = '
    <html>
        <head>
            <title>Thanks For Registering</title>
            <style>
                html {
                    height: 100%;
                }

                * {
                    margin: 0;
                    padding: 0;
                }

                body {
                    font: normal .80em \'Trebuchet MS\', Verdana, Arial, Helvetica, sans-serif;
                    background: #FFFFFF;
                    color: #555;
                    height: 100%;
                }

                #header {
                    background: #0082d2;
                    height: 110px;
                }

                #logo {
                    height: 140px;
                    margin-left: 50px;
                    background: transparent;
                }

                #logo #logo_text {
                    display: inline-block;
                    top: 10px;
                    left: 0;
                }

                #logo img {
                    height: 100px;
                    width: auto;
                }

                #footer {
                    position: fixed;
                    bottom: 0px;
                    width: 100%;
                    font-size: 100%;
                    height: 30px;
                    padding: 28px 0 5px 0;
                    text-align: center;
                    background: #0082d2;
                }

                #footer p {
                    color: white;
                    line-height: 1.7em;
                    padding: 0 0 10px 0;
                }

                .main {
                    margin: 15px 200px 15px 200px;
                }

                .main h2 {
                    margin-bottom: 10px;
                }

                .main h1 {
                    font-size: 170%;
                    padding: 10px 10px 10px 10px;
                }

                .main h2 {
                    font-size: 140%;
                    padding: 10px 10px 10px 10px;
                }

                .main h3 {
                    font-size: 130%;
                    padding: 20px 10px 20px 10px;
                }
            </style>
        </head>
        <body>
        <div id="header">
            <div id="logo">
                <div id="logo_text">
                    <img src="https://vpexam.com/img/logo_img.png" />
                </div>
            </div>
        </div>
        <div class="main">
            <h1>' . $fname . ', Thanks for signing up for your free VPExam account!</h1>
            <h2>If you set a password when you signed up, you may use that password, and
                login to your account <a href="https://vpexam.com/">here</a>.
            </h2>
        </div>
        <div id="footer">
            <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
        </div>
        </body>
    </html>
    ';

    $bodyPlain = $fname . ', Thanks for signing up for your free VPExam account!\r\n\r\nIf you set a password when you signed up, you may use that password, and
                login to your account at https://vpexam.com/.';

    // Create the message
    $message = Swift_Message::newInstance()
        ->setSubject('Thanks For Registering')
        ->setFrom(array('noreply@vpexam.com' => 'VPExam Registration Confirmation'))
        ->setTo(array($email))
        ->setBody($body, 'text/html')
        ->addPart($bodyPlain, 'text/plain');
}*/

