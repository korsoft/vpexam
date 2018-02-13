<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html" charset="UTF-8" http-equiv="content-type">
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/exam_main.css');?>">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/sweetalert.css');?>" />
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
        <script src="<?php autoVerCss('/js/exam_main.js');?>"></script>
        <script src="js/slideout.min.js"></script>
        <script type="text/javascript" src="/js/sweetalert.min.js"></script>
        <script src="<?php autoVerCss('/js/waiting_room.js');?>"></script>

        <script src="/js/pdf.js/pdf.js"></script>

        <title>Patient Overview</title>

        <style>
            .ui-dialog {
                font-family: encode-sans, 'Trebuchet MS', Verdana, Arial, Helvetica, sans-serif;
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

    <body>
        <script>
            var _numPhysicianId= <?php echo $_SESSION['user_id']; ?>;
        </script>
        <?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) :
            parse_str($_SERVER['QUERY_STRING']);
            $examParts = getExamParts($patientId, $examId, $mysqli);
            $patientInfo = getExtendedPatientInfo($patientId, $mysqli);
            $exam = getSingleExam($patientId, $examId, $mysqli);
            $symptoms = getSymptomsForExam($patientId, $examId, $mysqli);
            $examDateLocal = $exam->examDate;
            $examDateLocal->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $examDate = $examDateLocal->format('m/d/Y');
            $fullName = $patientInfo->name;
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

            $temp = $exam->temperature;
            $bp = ($exam->bpSystolic . '/' . $exam->bpDiastolic);
            $pulse = $exam->pulse;
            $respRate = $exam->respRate;
            $pulseOx = $exam->pulseOximetry;
            $painLevel = ($exam->painLevel . '/10');

            //Variable para el video_chat.php
            $calling = [
                'id'   => $patientId, 
                'name' => $fullName
            ];
        ?>

        <script type="text/javascript">
            <?php
                echo("setPatientGender(\"" . $patientInfo->gender . "\");");
                echo("setPhysicianId(" . $exam->physicianId . ");");
             ?>
        </script>
        <nav id="menu">
            <header>
                <div class="headerDiv">
                    <img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
                    <span class="menuHeaderText">VPExam</span>
                </div>
                <div class="btn" id="btnMenuMyPatients">My Patients</div>
                <div class="btn" id="btnMenuPatientOverview">Patient Overview</div>
                <div class="btn" id="btnMenuHistory">History</div>
                <div class="btn" id="btnMenuExam">Physical Exam</div>
                <div class="btn" id="btnMenuClipboard">Clipboard/Notes</div>
                <div class="btn" id="btnMenuSearch">Search</div>
                <div class="btn" id="btnMenuSettings">My Account Settings</div>
                <div class="btn" id="btnMenuLogout">Logout</div>
            </header>
        </nav>

        <main id="panel">
            <header>
                <img class="toggle-button" src="img/menu_white.png" width="40">
                <span class="headerText">EXAM OVERVIEW    |    <?php echo($examDate); ?></span>
                <div class="welcomeDiv">
                    Welcome, <?php echo($_SESSION['first_name']); ?>!
                </div>
            </header>
            <div class="mainContent">
                <div class="container">
                    <div class="left">
                        <div class="smallProfileDiv">
                            <?php if($_GET['wr']==1){?>
                                <a id="chat" href="#videochat">
                                    <label id="lblLinkWR">Go to Waiting room</label>
                                </a><br>
                            <?php }?>
                            <img id="profilePic" src="includes/getProfileImage.php?id=<?php echo($patientInfo->patientId); ?>&type=4">
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
                            <span class="title" id="symptomsTitle">Positive Review of Symptoms</span>
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
                    <div class="examsDiv">
                        <div class="modelDiv">
                            <img id="modelImg" src="images/<?php echo($gender === "Male" ? "male.jpg" : "female.jpg") ?>" />
                            <div class="keyDiv">
                                <div class="key">
                                    <img src="images/audio_icon.png" width="15" height="15"><span>Indicates this component of the exam contains audio.</span>
                                </div>
                                <div class="key">
                                    <img src="images/video_icon.png" width="15" height="15"><span>Indicates this component of the exam contains video.</span>
                                </div>
                            </div>
                            <div class="button-dark" id="btnRemoveExam">Remove Exam</div>
                            <div id="dialogConfirmDelete" title="Delete Exam?">
                                <img width="30" height="30" src="img/warn.png" style="float: left; margin: 0 5px 0 0;"><p>Deleting an exam will permanently delete all components including the
                                clipboard, audio and video. If you would like to keep any of these, you can download the exam before deleting it.<br /><br />Are you sure you want to delete the exam?</p>
                            </div>
                        </div>
                        <div class="tableDiv">
                            <table class="examComponentsTable">
                                <thead>
                                <tr>
                                    <th>EXAM COMPONENTS</th>
                                    <th> </th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $classVals = [];
                                        $ahrefVals = [];
                                        $indicesArr = [];
                                        //for ($i = 0; $i < count($examParts); $i++)
                                        //    array_push($indicesArr, $examPartsAbbrevReverse[$examParts[$i]]);
                                        //asort($indicesArr);

                                        /*for ($i = 0 ; $i < count($indicesArr); $i++) {
                                            $ahref = ((bool)$examPartsVideo[$indicesArr[$i]]) ? "exam_video_view.php" : "exam_audio_view.php";
                                            $ahref .= ("?patientId=".$patientInfo->patientId."&examId=".$exam->examId."&title=".urlencode($examPartsList[$indicesArr[$i]])."&idx=".$indicesArr[$i]."&abbrev=".$examPartsAbbrev[$indicesArr[$i]]);
                                            array_push($ahrefVals, $ahref);
                                        }*/
                                        $examPartsVideo = [];
                                        for ($i = 0; $i < count($examParts); $i++) {
                                            $examComponent = getExamComponentByAbbrev($examParts[$i], $mysqli);
                                            $isVid = ($examComponent->type === "v") ? true : false;
                                            $examPartsVideo[$i] = $isVid;
                                            $ahref = $isVid ? "exam_video_view.php" : "exam_audio_view.php";
                                            $ahref .= ("?patientId=" . $patientInfo->patientId . "&examId=" . $exam->examId . "&idx=" . $i . "&abbrev=" . $examComponent->abbrev);
                                            echo("<tr class=\"hoverableRow trExam\" data-link=\"$ahref\" id=\"$examComponent->id\" onmouseover=\"trMouseOver(this)\" onmouseout=\"trMouseOut(this)\" data-time=\"$examComponent->time\">");
                                            echo("  <td class=\"examPartName\">$examComponent->title</td>");
                                            echo("  <td><img src=\"images/" . ($isVid ? "video_icon.png" : "audio_icon.png") . "\" height=\"30\" width=\"30\" /></td>");
                                            echo("</tr>");
                                        }

                                        $_SESSION["examPartsVideo"] = $examPartsVideo;
                                        /*$_SESSION["examParts"] = $ahrefVals;
                                        $_SESSION["examPartsIndices"] = $indicesArr;
                                        $_SESSION["examPartsNames"] = $examPartsList;
                                        $_SESSION["allExamPartsAbbrevs"] = $examPartsAbbrev;
                                        $_SESSION["allExamPartsNames"] = $examPartsList;
                                        $_SESSION["examPartsVideo"] = $examPartsVideo;
                                        $classVals = array_values($classVals);
                                        for ($i = 0; $i < count($examParts); $i++) {
                                            $lId = $examPartsAbbrev[$indicesArr[$i]];
                                            $lPartName = $examPartsList[$indicesArr[$i]];
                                            $lIsVid = $examPartsVideo[$indicesArr[$i]];
                                            echo("<tr class=\"hoverableRow trExam\" data-link=\"$ahrefVals[$i]\" id=\"$lId\" onmouseover=\"trMouseOver(this)\" onmouseout=\"trMouseOut(this)\">");
                                            echo("  <td class=\"examPartName\">$lPartName</td>");
                                            echo("  <td><img src=\"images/" . ($lIsVid ? "video_icon.png" : "audio_icon.png") . "\" height=\"30\" width=\"30\" /></td>");
                                            echo("</tr>");
                                        }*/
                                    ?>
                                </tbody>
                            </table>
                            <table class="exam_documents_table">
                                <thead>
                                <tr>
                                    <th>EXAM DOCUMENTS</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $examDocument = getExamDocuments($exam->examId, $mysqli);
                                        for ($i = 0; $i < count($examDocument); $i++) {
                                            $aurl = "/".$exam->physicianId."/".$patientId."/".$examId."/documents/".$examDocument[$i]->filename;
                                            $ahref = "exam_document_view.php";
                                            $ahref .= ("?documentId=" . $examDocument[$i]->id . "&examId=" . $exam->examId ."&filename=" . $examDocument[$i]->filename);
                                            echo("<tr class=\"hoverableRow2 trDoc2\" id=\"id_".$examDocument[$i]->id."\">");
                                            echo("  <td class=\"examPartName2\">".$examDocument[$i]->name_document."</td>");
                                            echo("  <td>");
                                            echo("     <span id=\"idspan_".$examDocument[$i]->id."\" class=\"hide\">".$examDocument[$i]->name_document."</span>");
                                            echo("     <img data-id=\"".$examDocument[$i]->id."\" class=\"edit_document\" src=\"../images/pencil.jpg\" height=\"20\" width=\"20\"/>");
                                            echo("     <button data-id=\"".$examDocument[$i]->id."\" class=\"save button-dark-smaller\"><span class=\"glyphicon glyphicon-save\"></span> Save</button>");
                                            echo("     <button data-id=\"".$examDocument[$i]->id."\" class=\"cancel button-dark-smaller\"><span class=\"glyphicon glyphicon-save\"></span> Cancel</button>");
                                            echo("     <img class=\"view_document\" data-physician=\"".$exam->physicianId."\" data-patient=\"".$patientId."\" data-exam=\"".$examId."\" data-id=\"".$examDocument[$i]->id."\" src=\"../img/file-pdf.png\" height=\"20\" width=\"20\" />");
                                            echo("     <a target=\"_blank\" href=\"".$aurl."\"><img class=\"download_document\" data-id=\"".$examDocument[$i]->id."\" src=\"../images/download.png\" height=\"20\" width=\"20\" /></a>");
                                            echo("     <img class=\"remove_document\" data-id=\"".$examDocument[$i]->id."\" src=\"../images/trash.png\" height=\"20\" width=\"20\" />");
                                            echo("  </td>");
                                            echo("</tr>");
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    <?php else : ?>
        <p>
            <span class="error">You are not authorized to access this page.</span> Please <a href="main.php">login</a>.
        </p>
        
    <?php endif; ?>
    </body>
</html>
<?php 
if($_GET['wr']==1){
    include_once $_SERVER['DOCUMENT_ROOT'] .'/video_chat.php';
}
?>