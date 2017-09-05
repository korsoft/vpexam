<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html" charset="UTF-8" http-equiv="content-type">
    <link rel="stylesheet" type="text/css" href="style/patient_demographics.css">
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
    <script src="js/patient_demographics.js"></script>
    <script src="js/slideout.min.js"></script>
    <script src="https://tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <title>Patient Demographics</title>
</head>
<?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == false)) :
    parse_str($_SERVER['QUERY_STRING']);
    $patientInfo = getExtendedPatientInfo($patientId, $mysqli);
    $patientDemographics = getPatientDemographics($patientId, $mysqli);
    if ($patientDemographics === NULL) {
        $cellPhone = "-";
        $homePhone = "-";
        $workPhone = "-";
        $eContactName = "-";
        $eContactPh = "-";
        $insCarrier = "-";
        $insPolicyId = "-";
        $insGrpId = "-";
        $insClaimAddr = "-";
    } else {
        $cellPhone = ($patientDemographics->cellPhone === "" ? "-" : getFormattedPhone($patientDemographics->cellPhone));
        $homePhone = ($patientDemographics->homePhone === "" ? "-" : getFormattedPhone($patientDemographics->homePhone));
        $workPhone = ($patientDemographics->workPhone === "" ? "-" : getFormattedPhone($patientDemographics->workPhone));
        $eContactName = ($patientDemographics->emergencyContactName === "" ? "-" : $patientDemographics->emergencyContactName);
        $eContactPh = ($patientDemographics->emergencyContactPhone === "" ? "-" : getFormattedPhone($patientDemographics->emergencyContactPhone));
        $insCarrier = ($patientDemographics->insuraceCarrier === "" ? "-" : $patientDemographics->insuraceCarrier);
        $insPolicyId = ($patientDemographics->insurancePolicyId === "" ? "-" : $patientDemographics->insurancePolicyId);
        $insGrpId = ($patientDemographics->insuranceGroupId === "" ? "-" : $patientDemographics->insuranceGroupId);
        $insClaimAddr = ($patientDemographics->insuranceClaimAddr === "" ? "-" : $patientDemographics->insuranceClaimAddr);
    }

    $fullName = ($patientInfo->firstName . ($patientInfo->middleName === "" ? " " : (" " . $patientInfo->middleName . " ")) . $patientInfo->lastName);
    $email = $patientInfo->email;
    $gender = ($patientInfo->gender === "male") ? "Male" : "Female";
    $dob = $patientInfo->dob;
    if (is_null($dob))
        $dob = "-";
    else
        $dob = $patientInfo->dob->format('m/d/Y');
    if ($dob === "-")
        $age = "-";
    else
        $age = getAge($patientInfo->dob);

    $mrn = ($patientInfo->mrn === "" ? "-" : $patientInfo->mrn);
    $addr = ($patientInfo->address === "" ? "-" : $patientInfo->address);
    $city = ($patientInfo->city === "" ? "-" : $patientInfo->city);
    $state = ($patientInfo->state === "" ? "-" : $patientInfo->state);
    $zip = ($patientInfo->zip === "" ? "-" : $patientInfo->zip);
?>
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
        <span class="headerText">Patient Demographics</span>
        <div class="welcomeDiv">
            Welcome, <?php echo($_SESSION['first_name']); ?>!
        </div>
    </header>
    <div class="mainContent">
        <div class="container">
            <div class="smallProfileDiv">
                <img id="profilePic" src="includes/getProfileImage.php?id=<?php echo($patientInfo->patientId); ?>&type=1">
                <div class="patientInfoBox">
                    <span class="infoText infoTextName"><?php echo($fullName); ?></span>
                    <span class="infoText"><?php echo($gender); ?></span>
                    <span class="infoText"><?php echo($dob); ?></span>
                    <span class="infoText">Age <?php echo($age); ?></span>
                    <span class="infoText">MRN <?php echo($mrn); ?></span>
                </div>
            </div>
            <div class="demographicsDiv">
                <span class="title">Address</span>
                <table class="sideTable">
                    <tr>
                        <td class="sideTableTitleTd">Address</td>
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
                </table>
                <span class="title">Phone Numbers</span>
                <table class="sideTable">
                    <tr>
                        <td class="sideTableTitleTd">Cell</td>
                        <td><?php echo($cellPhone); ?></td>
                    </tr>
                    <tr>
                        <td class="sideTableTitleTd">Home</td>
                        <td><?php echo($homePhone); ?></td>
                    </tr>
                    <tr>
                        <td class="sideTableTitleTd">Work</td>
                        <td><?php echo($workPhone); ?></td>
                    </tr>
                </table>
                <span class="title">Emergency Contact</span>
                <table class="sideTable">
                    <tr>
                        <td class="sideTableTitleTd">Name</td>
                        <td><?php echo($eContactName); ?></td>
                    </tr>
                    <tr>
                        <td class="sideTableTitleTd">Phone</td>
                        <td><?php echo($eContactPh); ?></td>
                    </tr>
                </table>
                <span class="title">Email Address</span>
                <table class="sideTable">
                    <tr>
                        <td><?php echo($email); ?></td>
                    </tr>
                </table>
                <span class="title">Insurance Information</span>
                <table class="sideTable">
                    <tr>
                        <td class="sideTableTitleTd">Carrier</td>
                        <td><?php echo($insCarrier); ?></td>
                    </tr>
                    <tr>
                        <td class="sideTableTitleTd">Policy ID</td>
                        <td><?php echo($insPolicyId); ?></td>
                    </tr>
                    <tr>
                        <td class="sideTableTitleTd">Group ID</td>
                        <td><?php echo($insGrpId); ?></td>
                    </tr>
                    <tr>
                        <td class="sideTableTitleTd">Claim Address</td>
                        <td><?php echo($insClaimAddr); ?></td>
                    </tr>
                </table>
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