<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="description" content="WebRTC code samples">
        <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=1">
        <meta itemprop="description" content="Client-side WebRTC code samples">
        <meta itemprop="image" content="../../../images/webrtc-icon-192x192.png">
        <meta itemprop="name" content="WebRTC code samples">
        <meta name="mobile-web-app-capable" content="yes">
        <meta id="theme-color" name="theme-color" content="#ffffff">
        <base target="_blank">
        <title>VPExam - Patient Overview</title>
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
        <link rel="stylesheet" type="text/css" href="style/patient_view.css">
        <link rel="stylesheet" type="text/css" href="/style/sweetalert.css" />
        <link rel="manifest" href="/manifest.json">
    </head>
    <body>
        <?php
            if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) {
                parse_str($_SERVER['QUERY_STRING']);
                $pInfo = NULL;
                $exams = [];
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

                //Variable para el video_chat.php
                $calling = [
                    'id'   => $patientId, 
                    'name' => $fullName
                ];
        ?>
                <nav id="menu">
                    <header>
                        <div class="headerDiv">
                            <img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
                            <span class="menuHeaderText">VPExam</span>
                        </div>
                        <div class="btn" id="btnMenuPatientOverview">Patient Overview</div>
                        <div class="btn" id="btnMenuPatientDem">Patient Demographics</div>
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
                        <span class="headerText">PATIENT OVERVIEW</span>
                        <div class="welcomeDiv">
                            Welcome, <?php echo($_SESSION['first_name']); ?>!
                        </div>
                    </header>
                    <div class="mainContent">
                        <div class="container">
                            <div class="left">
                                <div class="smallProfileDiv">
                                    <img id="profilePic" src="includes/getProfileImage.php?id=<?php echo($patientId); ?>&type=1">
                                    <div class="patientInfoBox">
                                        <span class="infoText infoTextName"><?php echo($fullName); ?></span>
                                        <span class="infoText"><?php echo($gender); ?></span>
                                        <span class="infoText"><?php echo($dob); ?></span>
                                        <span class="infoText">Age <?php echo($age); ?></span>
                                        <span class="infoText"><?php echo($mrn); ?></span>
                                    </div>
                                </div>
                                <div class="demographicsDiv">
                                    <span class="title">Demographics</span>
                                    <table class="demographicsTable">
                                        <tr>
                                            <td class="demographicsTitleTd">Address</td>
                                            <td><?php echo($addr); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="demographicsTitleTd">City</td>
                                            <td><?php echo($city); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="demographicsTitleTd">State</td>
                                            <td><?php echo($state); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="demographicsTitleTd">Zip</td>
                                            <td><?php echo($zip); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Phone</td>
                                            <td><span style="font-size: 16px; font-weight: bold;"><?php echo($phoneType); ?></span> <?php echo($phone); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="examsDiv">
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
                </main>
        <?php } else { ?>
            <p><span class="error">You are not authorized to access this page.</span> Please <a href="/main.php">login</a>.</p>
        <?php } ?>
        <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="/js/slideout.min.js"></script>
        <script type="text/javascript" src="/js/moment-with-locales.min.js"></script>
        <script type="text/javascript" src="/js/moment-timezone-with-data.min.js"></script>
        <script type="text/javascript" src="/js/patient_view.js"></script>
        <script type="text/javascript" src="/js/sweetalert.min.js"></script>
        <script type="text/javascript" src="/js/waiting_room.js"></script>
        <script>
            setPatientId(<?php echo($patientId); ?>);
        </script>
        <?php include 'video_chat.php';?>
    </body>
</html>