<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();

    $waitingroom = getPatientsFromWaitingRoom($mysqli, $_SESSION['user_id']);

    $numPage     = intval(empty($_GET['page'])?0:$_GET['page'] );
    $numPage     = $numPage<0?0:$numPage; 
    $numLimit    = 20;
    $numOffSet   = ($numPage)*$numLimit; 
    $blnHasNext  = false;
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
        <title>VPExam - Physician Main Page</title>
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
        <link rel="stylesheet" type="text/css" href="/style/physician_main.css" />
        <link rel="stylesheet" type="text/css" href="/style/sweetalert.css" />
        <link rel="manifest" href="/manifest.json" />
    </head>
    <body>
        <nav id="menu">
            <header>
                <div class="headerDiv">
                    <img class="menuHeaderImg" src="img/logo_img_no_text.png" height="50">
                    <span class="menuHeaderText">VPExam</span>
                </div>
                <?php if(0 < count($waitingroom)) { ?>
                    <div class="btn waitingRoom" data-id="<?php echo $_SESSION['user_id']; ?>">
                        Waiting Room
                        <ul>
                            <?php
                                foreach ($waitingroom as $patient) {
                                    echo "<li id=\"menu-li-patient{$patient['id']}\" data-id=\"{$patient['id']}\"><img src=\"/img/remove.png\" alt=\"X\" class=\"removeFromWR\" /><label>{$patient['name']}</label></li>";
                                }
                            ?>
                        </ul>
                    </div>
                <?php } ?>
                <div class="btn" id="btnMenuMyPatients">My Patients</div>
                <div class="btn" id="btnMenuSearch">Search</div>
                <div class="btn" id="btnMenuSettings">My Account Settings</div>
                <div class="btn" id="btnMenuLogout">Logout</div>
            </header>
        </nav>
        <main id="panel">
            <header>
                <img class="toggle-button" src="img/menu_white.png" width="40">
                <span class="headerText">MY PATIENTS</span>
                <div class="welcomeDiv">
                    Welcome, <?php echo($_SESSION['first_name']); ?>!
                </div>
            </header>
            <div class="tableHeaderDiv">
                <table class="tableHeader">
                    <thead>
                    <tr>
                        <th class="shortColumn">FIRST NAME</th>
                        <th class="shortColumn">LAST NAME</th>
                        <th class="shortColumn">DOB</th>
                        <th class="longColumn">CONTACT INFO</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="mainContent">
                <table class="tableContent">
                    <tbody>
                        <?php
                            $patientInfos = getPatientsOfPhysicianAndRemoveNotDisplay($_SESSION['user_id'], $mysqli, $numOffSet, $numLimit+1);
                            $num = count($patientInfos);
                            if( $num > $numLimit ){
                                $blnHasNext = true;
                                array_pop($patientInfos);
                            }
                            foreach ($patientInfos as $info) {
                                $fname = $info->firstName;
                                $lname = $info->lastName;
                                $id = $info->patientId;
                                $mrn = $info->mrn;
                                $gender = ($info->gender === "male") ? "Male" : "Female";
                                $dob = $info->dob->format('m/d/Y');
                                $phone = getFormattedPhone($info->phone);
                                $phType = strtoupper($info->phoneType);
                                $address = $info->address;
                                $city = $info->city;
                                $state = $info->state;
                                $zip = $info->zip;
                                $fullAddr = $address . ", " . $city . ", " . $state . " " . $zip;
                                $phAndType = $phType . ": " . $phone;

                                $col1 =
                                    "<td class=\"shortColumn\">
                                        <img class=\"patientProfilePic\" src=\"includes/getProfileImage.php?id=$id&type=1\">
                                        <div class=\"nameMRNDiv\">
                                            <div style=\"margin: 20px 0 0 0;\">
                                                <div>$fname</div>
                                                <div>$mrn</div>
                                            </div>
                                        </div>
                                    </td>
                                    ";

                                $col2 = "<td class=\"shortColumn\">$lname</td>";
                                $col3 =
                                    "
                                    <td class=\"shortColumn\">
                                        <div class=\"nameMRNDiv\">
                                            <div style=\"margin: 20px 0 0 0;\">
                                                <div>$dob</div>
                                                <div>$gender</div>
                                            </div>
                                        </div>
                                    </td>
                                    ";
                                $col4 =
                                    "<td class=\"longColumn\">
                                        <div class=\"nameMRNDiv\">
                                            <div style=\"margin: 20px 0 0 0;\">
                                                <div>$fullAddr</div>
                                                <div>$phAndType</div>
                                            </div>
                                        </div>
                                    </td>
                                    ";
                                $col5 =
                                    "<td>
                                        <div class=\"removePatientOuter\" id=\"$id\">
                                            <div class=\"removePatientInner\">X</div>
                                        </div>
                                     </td>
                                    ";
                                $href = "patient_view.php?patientId=" . $id;

                                print("
                                <tr class=\"hoverableRow patientsTable\" id=\"$id\">
                                    $col1
                                    $col2
                                    $col3
                                    $col4
                                    $col5
                                </tr>
                                ");
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" align='right'>
                                <?php 
                                if($numPage > 0 ){
                                    $strBack = '/physician_main.php'; 
                                    if($numPage > 1){
                                        $strBack .= '?page=' . ($numPage -1 ); 
                                    }
                                ?>
                                <a class='button_pagination' href="<?php echo $strBack ;?>" target='_self'>&laquo; Back</a>
                                <?php 
                                }
                                if($blnHasNext ){  
                                ?>
                                <a class='button_pagination' href="/physician_main.php?page=<?php echo $numPage+1;?>" target='_self'>Next &raquo;</a>
                                <?php } ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </main>
        <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="/js/physician_main.js"></script>
        <script type="text/javascript" src="/js/slideout.min.js"></script>
        <script type="text/javascript" src="/js/sweetalert.min.js"></script>
        <script type="text/javascript" src="/js/waiting_room.js"></script>
        <script type="text/javascript">
            setPhysicianId(<?php echo $_SESSION['user_id'] ?>);
            $(document).ready(function() {
                var slideout = new Slideout({
                    'panel': $('#panel')[0],
                    'menu': $('#menu')[0],
                    'padding': 256,
                    'tolerance': 70
                });
                $('.toggle-button').on('click', function() {
                    slideout.toggle();
                });
            });
        </script>
        <?php include 'video_chat.php';?>
    </body>
</html>
