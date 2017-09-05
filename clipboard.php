<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html" charset="UTF-8" http-equiv="content-type">
    <link rel="stylesheet" type="text/css" href="style/clipboard.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta http-equiv="cleartype" content="on">
    <meta name="MobileOptimized" content="320">
    <meta name="HandheldFriendly" content="True">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <script src="https://code.jquery.com/jquery-latest.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="js/clipboard.js"></script>
    <script src="js/slideout.min.js"></script>
    <script src="https://tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script src="js/collapsible-list.js"></script>
    <title>Clipboard</title>

    <style>
        .ui-dialog {
            font-family: encode-sans, 'Trebuchet MS', Verdana, Arial, Helvetica, sans-serif;
            padding: 0;
            margin: 0.5em 1em;
            z-index: 10000000000 !important;
        }
        .ui-widget-header {
            background: #0082d2;
            color: white;
            font-weight: bold;
        }
        .ui-widget-content {
            color: #555;
        }
    </style>
</head>
<?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) :
    parse_str($_SERVER['QUERY_STRING']);
    $exam = getSingleExam($patientId, $examId, $mysqli);
    $patientInfo = getExtendedPatientInfo($patientId, $mysqli);
    $history = getHistoryForExam($patientId, $examId, $mysqli);
    $examPartsIndices = $_SESSION["examPartsIndices"];
    $examPartsNames = $_SESSION["examPartsNames"];
    $allExamPartsAbbrevs = $_SESSION["allExamPartsAbbrevs"];
    $allExamPartsNames = $_SESSION["allExamPartsNames"];
    $examPartsVideo = $_SESSION["examPartsVideo"];
    $_SESSION["currentExamPart"] = $idx;
    $title = "Patient History";
    $examInfo = getSingleExam($patientId, $examId, $mysqli);
    $examParts = getExamParts($patientId, $examId, $mysqli);
    $patientInfo = getExtendedPatientInfo($patientId, $mysqli);
    $symptoms = getSymptomsForExam($patientId, $examId, $mysqli);
    $examDate = $examInfo->examDate->format('m/d/Y');
    $examPartsIndexed = array_values($examParts);
    $fullName = ($patientInfo->firstName . ($patientInfo->middleName === "" ? " " : (" " . $patientInfo->middleName . " ")) . $patientInfo->lastName);
    $gender = ($patientInfo->gender === "male") ? "Male" : "Female";
    $dob = $patientInfo->dob->format('m/d/Y');
    $age = getAge($patientInfo->dob);
    $mrn = $patientInfo->mrn;
    $addr = $patientInfo->address;
    $city = $patientInfo->city;
    $state = $patientInfo->state;
    $zip = $patientInfo->zip;
    $phoneType = strtoupper($patientInfo->phoneType);
    $phone = getFormattedPhone($patientInfo->phone);

    $incline = $examInfo->incline;
    $posture = $examInfo->posture;
    $temp = $examInfo->temperature;
    $bp = ($examInfo->bpSystolic . '/' . $examInfo->bpDiastolic);
    $pulse = $examInfo->pulse;
    $respRate = $examInfo->respRate;
    $pulseOx = $examInfo->pulseOximetry;
    $painLevel = ($examInfo->painLevel . '/10');
?>
<script type="text/javascript">
    <?php
        echo('setExamParts("' . urlencode(json_encode(array_values($_SESSION["examParts"]))) . '");');
        echo('setAllExamPartsAbbrevs("' . urlencode(json_encode(array_values($allExamPartsAbbrevs))) . '");');
        echo('setAllExamPartsNames(\''.json_encode(array_values($allExamPartsNames)) .'\');');
        echo('setExamPartsIndices("' . urlencode(json_encode(array_values($examPartsIndices))) . '");');
        echo('setCurrentExamPartAbbrev("' . $allExamPartsAbbrevs[$idx] . '");');
        echo('setPhysician(' . $examInfo->physicianId . ');');
        echo('setPatientId(' . $patientId . ');');
        echo('setExamId(' . $examId . ');');
    ?>
</script>
<body>
<nav id="menu">
    <header>
        <div class="headerDiv">
            <img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
            <span class="menuHeaderText">VPExam</span>
        </div>
        <div class="btn" id="btnMenuPatientOverview">Patient Overview</div>
        <div class="btn" id="btnMenuPatientDem">Patient Demographics</div>
        <div class="btn" id="btnMenuHistory">History</div>
        <div class="btn" id="btnMenuExam">Physical Exam</div>
        <div class="btn" id="btnMenuClipboard">Clipboard/Notes</div>
        <div class="btn" id="btnMenuConnectDoxy">Connect via Doxy.me</div>
        <div class="btn" id="btnMenuMyPatients">My Patients</div>
        <div class="btn" id="btnMenuSearch">Search</div>
        <div class="btn" id="btnMenuSettings">My Account Settings</div>
        <div class="btn" id="btnMenuLogout">Logout</div>
    </header>
</nav>
<main id="panel">
    <header>
        <img class="toggle-button" src="img/menu_white.png" width="40">
        <span class="headerText">Clipboard</span>
        <div class="welcomeDiv">
            Welcome, <?php echo($_SESSION['first_name']); ?>!
        </div>
    </header>
    <div class="mainContent">
        <div class="container">
            <div class="left">
                <div class="smallProfileDiv">
                    <img id="profilePic" src="includes/getProfileImage.php?id=<?php echo($patientInfo->patientId); ?>&type=1">
                    <div class="patientInfoBox">
                        <span class="infoText infoTextName"><?php echo($fullName);  ?></span>
                        <span class="infoText"><?php echo($gender); ?></span>
                        <span class="infoText"><?php echo($dob); ?></span>
                        <span class="infoText">Age <?php echo($age); ?></span>
                        <span class="infoText"><?php echo($mrn); ?></span>
                    </div>
                </div>
                <div class="demographicsDiv">
                    <span class="title">Demographics</span>
                    <table class="sideTable">
                        <tr>
                            <td class="demographicsTitleTd">Address</td>
                            <td><?php echo($addr); ?></td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">City</td>
                            <td><?php echo($city); ?></td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">State</td>
                            <td><?php echo($state); ?></td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Zip</td>
                            <td><?php echo($zip); ?></td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td><span style="font-size: 16px; font-weight: bold;"><?php echo($phoneType); ?></span> <?php echo($phone); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="vitalsDiv">
                    <span class="title">Vitals</span>
                    <table class="sideTable">
                        <tr>
                            <td class="sideTableTitleTd">Incline</td>
                            <td><?php echo($incline); ?>°</td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Posture</td>
                            <td><?php echo($posture); ?></td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Temperature</td>
                            <td><?php echo($temp); ?>°F</td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Blood Pressure</td>
                            <td><?php echo($bp); ?></td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Pulse</td>
                            <td><?php echo($pulse); ?> bpm</td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Respiratory Rate</td>
                            <td><?php echo($respRate); ?> breaths/min</td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">O<sub>2</sub> Saturation</td>
                            <td><?php echo($pulseOx); ?>%</td>
                        </tr>
                        <tr>
                            <td class="sideTableTitleTd">Pain Level</td>
                            <td><?php echo($painLevel); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="symptomsDiv">
                    <span class="title" id="symptomsTitle">Symptoms</span>
                    <?php
                        if (count($symptoms) === 0) {
                            echo("<span class=\"symptom\">No reported symptoms</span>");
                        } else {
                            for ($i = 0; $i < count($symptoms); $i++) {
                                $symptom = $symptoms[$i]->description;
                                echo("<span class=\"symptom\">$symptom</span>");
                            }
                        }
                    ?>
                </div>
            </div>
            <div class="right">
                <div class="clipboardDiv">
                    <div style="display: block; height: 40px;">
                        <div style="display: inline-block; float: left;">
                            <div class="title" style="display: block;">Clipboard</div>
                            <div class="subTitle" id="lastSavedMain">Last Saved: Not Saved</div>
                        </div>
                        <div style="float: right;">
                            <div class="button-dark" id="btnInsertNormalMain">Insert Normal</div>
                            <div class="button-dark" id="btnSaveClipboardMain">Save</div>
                        </div>
                    </div>
                    <textarea id="taClipboardMain"></textarea>
                    <div class="copyToSystemClipboardDiv">
                        <div class="title" style="display: block; margin: 0 0 10px 0;">Copy Clipboard Text To System Clipboard As:</div>
                    </div>
                    <div>
                        <div class="button-dark" id="btnCopyPlainText">Plain Text</div>
                        <div class="button-dark" id="btnCopyHTML">HTML</div>
                    </div>
                    <div id="copyDialog" title="Copy To System Clipboard">
                        <span class="copyDialogText">Press Ctrl+C (Command+C on Mac) to copy the text to your system clipboard.</span>
                        <textarea id="copyText"></textarea>
                        <div style="float: right;">
                            <div class="button-dark" id="btnCopyDialogOk">Ok</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
<?php else : ?>
    <p>
        <span class="error">You are not authorized to access this page.</span> Please <a href="main.php">login</a>.
    </p>
<?php endif; ?>
</html>