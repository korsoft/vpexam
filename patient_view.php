<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';
    sec_session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
    $_strPageTitle       = 'VPExam - Patient Overview';
    $_strHeaderTitle     = 'PATIENT OVERVIEW';
    $_arrStyles[]        = '/style/patient_view.css';

    $_arrScripts[]       = '/js/moment-with-locales.min.js';
    $_arrScripts[]       = '/js/moment-timezone-with-data.min.js';
    $_arrScripts[]       = '/js/patient_view.js';
    // 
    $_arrScripts[]   = '/js/waiting_room.js';
    //$_arrScripts[]   = '/js/single_waiting_room.js';

    $_blnShowPatientMenu = true;

    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';

    parse_str($_SERVER['QUERY_STRING']);
    $pInfo = NULL;
    $exams = [];
    $_arrFunctionsExec   = array(
                            ( 'setPatientId(' . $patientId . ');'),
                           );
    if (!is_null($patientId)) {
        $pInfo = getExtendedPatientInfo($patientId, $mysqli);
        $exams = getPatientExams($patientId, $mysqli);
    }
    $fullName  = $pInfo->name;
    $gender    = ($pInfo->gender === "male") ? "Male" : "Female";
    $dob       = $pInfo->dob->format('m/d/Y');
    $age       = getAge($pInfo->dob);
    $mrn       = $pInfo->mrn;
    $addr      = $pInfo->address;
    $city      = $pInfo->city;
    $state     = $pInfo->state;
    $zip       = $pInfo->zip;
    $phoneType = strtoupper($pInfo->phoneType);
    $phone     = getFormattedPhone($pInfo->phone);
    $email = $pInfo->email;

    //Variable para el video_chat.php
    $calling = [
        'id'   => $patientId, 
        'name' => $fullName
    ];
?>
<script>
    var _numPhysicianId= <?php echo $_SESSION['user_id']; ?>;
</script>
                    <div class="mainContent">
                        <div class="container">
                            <div class="left">
                                <div class="smallProfileDiv">
                                    <?php if($_GET['wr']==1){?>
                                    <a id="chat" href="#videochat">
                                        <label id="lblLinkWR">Go to Waiting room</label>
                                    </a><br>
                                    <?php }?>
                                    <img id="profilePic" src="includes/getProfileImage.php?id=<?php echo($patientId); ?>&type=4">
                                    <div class="patientInfoBox">
                                        <span class="infoText infoTextName"><?php echo($fullName); ?></span>
                                        <span class="infoText"><?php echo($gender); ?></span>
                                        <span class="infoText"><?php echo($dob); ?></span>
                                        <span class="infoText">Age <?php echo($age); ?></span>
                                        <span class="infoText">Phone: <?php echo($phoneType); ?></span> <?php echo($phone); ?>
                                        <span class="infoText">Email: <?php echo($email); ?></span>
                                        <span class="infoText"><?php echo($mrn); ?></span>
                                    </div>
                                </div>
                                <!--div class="demographicsDiv">
                                    <span class="title">Demographics</span>
                                    <table class="demographicsTable">
                                        <tr>
                                            <td class="demographicsTitleTd">Address</td>
                                            <td><?php //echo($addr); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="demographicsTitleTd">City</td>
                                            <td><?php //echo($city); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="demographicsTitleTd">State</td>
                                            <td><?php //echo($state); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="demographicsTitleTd">Zip</td>
                                            <td><?php //echo($zip); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Phone</td>
                                            <td><span style="font-size: 16px; font-weight: bold;"><?php //echo($phoneType); ?></span> <?php //echo($phone); ?></td>
                                        </tr>
                                    </table>
                                </div-->
                            </div>
                            <div class="examsDiv">
                                <input id="inputwr" type="hidden" value="<?php echo $_GET['wr']; ?>">
                                <?php
                                    if (is_null($exams) || count($exams) === 0) {
                                        echo('<span style="font-size: 20px; font-weight: bold;">No exams available</span>');
                                    } else {
                                        echo('
                                            <table class="examListTable">
                                                <thead>
                                                    <tr>
                                                        <th>EXAM DATE</th>
                                                        <th>LAST VIEWED</th>
                                                        <th>COMPLETED</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                        ');
                                        for ($i = 0; $i < count($exams); $i++) {
                                            $singleExam = $exams[$i];
                                            $id = $singleExam->examId;
                                            $examDateLocal = $singleExam->examDate;
                                            $examDateLocal->setTimezone(new DateTimeZone(date_default_timezone_get()));
                                            $examDate = $examDateLocal->format('m/d/Y h:i A');

                                            $td1 = "<td class='examDate'>$examDate</td>";
                                            $td2 = "<td>-</td>";
                                            $td3 = "<td>No</td>";

                                            $tr = "
                                                    <tr class=\"hoverableRow trExam\" id=\"$id\">
                                                        $td1
                                                        $td2
                                                        $td3
                                                    </tr>
                                            ";

                                            echo($tr);
                                        }
                                        echo('
                                            </tbody>
                                        </table>
                                        ');
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

<?php
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/footer_physician.php';
    if($_GET['wr']==1){
        include_once $_SERVER['DOCUMENT_ROOT'] .'/video_chat.php';
    }
?>