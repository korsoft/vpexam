<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html" charset="UTF-8" http-equiv="content-type">
    <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/exam_video_view.css');?>">
    <link href="https://vjs.zencdn.net/4.12/video-js.css" rel="stylesheet" type="text/css">
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
    <script src="https://vjs.zencdn.net/4.12/video.js"></script>
    <script src="<?php autoVerCss('/js/exam_video_view.js');?>"></script>
    <script src="js/slideout.min.js"></script>
    <script src="https://tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script src="js/jquery.fullscreen.js"></script>
    <script src="js/jquery.cookie.js"></script>
    <title>Exam Video View</title>

    <style>
        .ui-resizable-s {
            background: #E9E9E9;
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
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
<?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) :
    parse_str($_SERVER['QUERY_STRING']);
    $examPartsVideo = $_SESSION["examPartsVideo"];
    $_SESSION["currentExamPart"] = $idx;
    $examComponent = getExamComponentByAbbrev($abbrev, $mysqli);
    $title = $examComponent->title;
    $examInfo = getSingleExam($patientId, $examId, $mysqli);
    $examParts = getExamParts($patientId, $examId, $mysqli);
    $patientInfo = getExtendedPatientInfo($patientId, $mysqli);
    $fullName = $patientInfo->name;
    $dob = $patientInfo->dob->format('m/d/Y');

    $videoURL = '';
    // Generate token that will be used to get access to video url
    $token = generateToken($mysqli, $examInfo->physicianId);

    if($token['success']) {
        // We've successfully requested and gotten a token to play the video
        $videoURL = 'includes/getMedia.php?token=' . $token['token'] . '&physicianId=' . $examInfo->physicianId . '&patientId=' . $patientInfo->patientId . '&examId=' . $examInfo->examId . '&abbrev=' . $abbrev . '&type=v';
    }
?>
<script type="text/javascript">
    <?php
        echo('setExamParts("' . urlencode(json_encode($examParts)) . '");');
        echo('setExamPartsVideo("' . urlencode(json_encode(array_values($examPartsVideo))) . '");');
        echo('setPhysician(' . $examInfo->physicianId . ');');
        echo('setPatientId(' . $patientId . ');');
        echo('setExamId(' . $examId . ');');

        // Set the jump table
        $jumpTableHTML = "";
        for ($i = 0; $i < count($examParts); $i++) {
            $ec = getExamComponentByAbbrev($examParts[$i], $mysqli);
            $localName = $ec->title;
            $localAbbrev = $ec->abbrev;
            $jumpTableHTML .= "<tr class=\"jumpTableTr\" id=\"$localAbbrev\"><td>$localName</td></tr>";
        }
        echo('setJumpTableHTML("' . urlencode($jumpTableHTML) . '");');
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
        <img class="toggle-button" src="img/menu_white.png" width="40px">
        <div class="patientInfoTopDiv">
            <img class="patientProfilePicSmall" src="includes/getProfileImage.php?id=<?php echo($patientInfo->patientId); ?>&type=1">
            <span class="patientNameTop"><?php echo($fullName); ?></span>
            <span class="topDivider">|</span>
            <span class="patientBirthdayTop"><?php echo($dob); ?></span>
        </div>
        <span class="headerText"><?php echo($examComponent->title); ?></span>
        <div class="welcomeDiv">Welcome, <?php echo($_SESSION['first_name']); ?>!</div>
    </header>
    <div class="mainContent">
        <div class="container">
            <div class="main">
                <div class="videoDiv">
                    <video class="video-js vjs-default-skin vjs-big-play-centered" data-setup='{"example_option":true}' id="examVid" width="100%" height="400" controls loop autoplay muted>
                        <source src="<?php echo($videoURL); ?>" type="video/mp4">
                        <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
                    </video>
                </div>
                <div class="controlsDiv">
                    <div class="title" style="display: block; margin: 0 0 10px 0;">Controls</div>
                    <div class="button-dark-image" id="btnPrevComponentMain">
                        <img src="img/skip_prev.png" height="25" style="margin: 0 5px 0 0;">
                        <span class="imgButtonSpan">Previous</span>
                    </div>
                    <div class="button-dark-image" id="btnJumpMain">
                        <img src="img/jump.png" height="25" style="margin: 0 5px 0 0;">
                        <span class="imgButtonSpan">Jump</span>
                    </div>
                    <div id="jumpDialogMain" title="Jump">
                        <table class="jumpTable">
                            <?php
                                echo($jumpTableHTML);
                            ?>
                        </table>
                    </div>
                    <div class="button-dark-image" id="btnNextComponentMain">
                        <img src="img/skip_next.png" height="25" style="margin: 0 5px 0 0;">
                        <span class="imgButtonSpan">Next</span>
                    </div>
                </div>
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
                </div>
                <div id="cantMoveDialog"></div>
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
