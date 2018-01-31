<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();
?>
<html>
    <head>
        <meta name="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">

        <meta name="description" content="VPExam - Patient">
        <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=1">
        <meta itemprop="description" content="VPExam - Patient">
        <meta itemprop="image" content="../../../images/webrtc-icon-192x192.png">
        <meta itemprop="name" content="VPExam - Patient">
        <meta name="mobile-web-app-capable" content="yes">
        <meta id="theme-color" name="theme-color" content="#ffffff">

        <base target="_blank">

        <title>VPExam - Patient Main Page</title>

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
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/style.css');?>" />
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/patient_main.css');?>" />
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/sweetalert.css');?>" />
        <link rel="stylesheet" type="text/css" href="/js/jQueryFileUpload/css/jquery.fileupload.css" />
        <link rel="manifest" href="/manifest.json" />
    </head>
    <body>
        <?php if (isset($_SESSION['user_id']) && 0 < $_SESSION['user_id'] && $_SESSION['is_patient'] == true) {
            $patientInfo = getExtendedPatientInfo($_SESSION['user_id'], $mysqli);
            $physicianWRInfo = getPhysicianInfoWR($_SESSION['user_id'], $mysqli);
            
            $physician = true == $physicianWRInfo['success']?$physicianWRInfo['data']:false;
            $strWR = '';
            if(is_array($physician)) {
                $strWR = ($physician['waiting_room']!=null)?$physician['waiting_room']:'';
            }
        ?>
            <div id="header">
                <div id="logo">
                    <div id="logo_text"><img src="img/logo_img.png" /></div>
                    <div class="topmenu">
                        <div class="topmenuItem hoverable" id="btnMenuLogout">
                            <span>Logout</span>
                        </div>
                    </div>
                    <div class="welcomeDiv">
                        Welcome, <?php echo "{$_SESSION['first_name']} {$_SESSION['last_name']}" ?>!
                    </div>
                </div>
            </div>
            <div class="main">
                <div class="profile">
                    <div class="profileInner">
                        <div class="profileImgDiv">
                            <img id="profileImg" src="includes/getProfileImage.php?id=<?php echo ($patientInfo->patientId); ?>&type=4" alt="" />
                            <form action="/includes/upload_image.php" id="myForm" name="frmupload" method="post" enctype="multipart/form-data">
                                <input type="hidden" id="id" value="<?php echo ($patientInfo->patientId); ?>" />
                                <input type="hidden" id="wr" value="<?php echo $strWR; ?>" />
                                <input type="file" name="files[]" id="fileToUpload" style="display: none;">
                                <div class="button-dark" id="btnChangeImg">Change Profile Picture</div>
                            </form>
                            <div id="progress" class="progress">
                                <div class="progress-bar progress-bar-success"></div>
                            </div>
                            <!-- The container for the uploaded files -->
                            <div id="files" class="files"></div>
                        </div>
                        <div class="profileText">
                            <h2>My Profile</h2>
                            <table>
                                <tbody>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Name</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->name); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">MRN</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->mrn); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">DOB</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->dob->format('m/d/Y')); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Gender</span></td>
                                    <td>
                                        <select class="profileTextData">
                                            <?php
                                                echo('<option '.(($patientInfo->gender === "male") ? 'selected="selected"' : '').' value="m">Male</option>');
                                                echo('<option '.(($patientInfo->gender === "female") ? 'selected="selected"' : '').' value="f">Female</option>');
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Email</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->email); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Primary Phone</span></td>
                                    <td>
                                        <span class="profileTextData">
                                            <?php
                                                $phone = $patientInfo->phone;
                                                if (strlen($phone) == 10) {
                                                    $areaCode = substr($phone, 0, 3);
                                                    $prefix = substr($phone, 3, 3);
                                                    $lineNum = substr($phone, 6);
                                                    $num = '('.$areaCode.')-'.$prefix.'-'.$lineNum;
                                                    echo($num);
                                                }
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Secondary Phone</span></td>
                                    <td><span class="profileTextData">-</span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Address</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->address); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">City</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->city); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">State</span></td>
                                    <td>
                                        <select class="profileTextData">
                                            <option <?php if($patientInfo->state === "AL") echo('selected="selected"'); ?> value="AL">Alabama</option>
                                            <option <?php if($patientInfo->state === "AK") echo('selected="selected"'); ?> value="AK">Alaska</option>
                                            <option <?php if($patientInfo->state === "AZ") echo('selected="selected"'); ?> value="AZ">Arizona</option>
                                            <option <?php if($patientInfo->state === "AR") echo('selected="selected"'); ?> value="AR">Arkansas</option>
                                            <option <?php if($patientInfo->state === "CA") echo('selected="selected"'); ?> value="CA">California</option>
                                            <option <?php if($patientInfo->state === "CO") echo('selected="selected"'); ?> value="CO">Colorado</option>
                                            <option <?php if($patientInfo->state === "CT") echo('selected="selected"'); ?> value="CT">Connecticut</option>
                                            <option <?php if($patientInfo->state === "DE") echo('selected="selected"'); ?> value="DE">Delaware</option>
                                            <option <?php if($patientInfo->state === "DC") echo('selected="selected"'); ?> value="DC">District Of Columbia</option>
                                            <option <?php if($patientInfo->state === "FL") echo('selected="selected"'); ?> value="FL">Florida</option>
                                            <option <?php if($patientInfo->state === "GA") echo('selected="selected"'); ?> value="GA">Georgia</option>
                                            <option <?php if($patientInfo->state === "HI") echo('selected="selected"'); ?> value="HI">Hawaii</option>
                                            <option <?php if($patientInfo->state === "ID") echo('selected="selected"'); ?> value="ID">Idaho</option>
                                            <option <?php if($patientInfo->state === "IL") echo('selected="selected"'); ?> value="IL">Illinois</option>
                                            <option <?php if($patientInfo->state === "IN") echo('selected="selected"'); ?> value="IN">Indiana</option>
                                            <option <?php if($patientInfo->state === "IA") echo('selected="selected"'); ?> value="IA">Iowa</option>
                                            <option <?php if($patientInfo->state === "KS") echo('selected="selected"'); ?> value="KS">Kansas</option>
                                            <option <?php if($patientInfo->state === "KY") echo('selected="selected"'); ?> value="KY">Kentucky</option>
                                            <option <?php if($patientInfo->state === "LA") echo('selected="selected"'); ?> value="LA">Louisiana</option>
                                            <option <?php if($patientInfo->state === "ME") echo('selected="selected"'); ?> value="ME">Maine</option>
                                            <option <?php if($patientInfo->state === "MD") echo('selected="selected"'); ?> value="MD">Maryland</option>
                                            <option <?php if($patientInfo->state === "MA") echo('selected="selected"'); ?> value="MA">Massachusetts</option>
                                            <option <?php if($patientInfo->state === "MI") echo('selected="selected"'); ?> value="MI">Michigan</option>
                                            <option <?php if($patientInfo->state === "MN") echo('selected="selected"'); ?> value="MN">Minnesota</option>
                                            <option <?php if($patientInfo->state === "MS") echo('selected="selected"'); ?> value="MS">Mississippi</option>
                                            <option <?php if($patientInfo->state === "MO") echo('selected="selected"'); ?> value="MO">Missouri</option>
                                            <option <?php if($patientInfo->state === "MT") echo('selected="selected"'); ?> value="MT">Montana</option>
                                            <option <?php if($patientInfo->state === "NE") echo('selected="selected"'); ?> value="NE">Nebraska</option>
                                            <option <?php if($patientInfo->state === "NV") echo('selected="selected"'); ?> value="NV">Nevada</option>
                                            <option <?php if($patientInfo->state === "NH") echo('selected="selected"'); ?> value="NH">New Hampshire</option>
                                            <option <?php if($patientInfo->state === "NJ") echo('selected="selected"'); ?> value="NJ">New Jersey</option>
                                            <option <?php if($patientInfo->state === "NM") echo('selected="selected"'); ?> value="NM">New Mexico</option>
                                            <option <?php if($patientInfo->state === "NY") echo('selected="selected"'); ?> value="NY">New York</option>
                                            <option <?php if($patientInfo->state === "NC") echo('selected="selected"'); ?> value="NC">North Carolina</option>
                                            <option <?php if($patientInfo->state === "ND") echo('selected="selected"'); ?> value="ND">North Dakota</option>
                                            <option <?php if($patientInfo->state === "OH") echo('selected="selected"'); ?> value="OH">Ohio</option>
                                            <option <?php if($patientInfo->state === "OK") echo('selected="selected"'); ?> value="OK">Oklahoma</option>
                                            <option <?php if($patientInfo->state === "OR") echo('selected="selected"'); ?> value="OR">Oregon</option>
                                            <option <?php if($patientInfo->state === "PA") echo('selected="selected"'); ?> value="PA">Pennsylvania</option>
                                            <option <?php if($patientInfo->state === "RI") echo('selected="selected"'); ?> value="RI">Rhode Island</option>
                                            <option <?php if($patientInfo->state === "SC") echo('selected="selected"'); ?> value="SC">South Carolina</option>
                                            <option <?php if($patientInfo->state === "SD") echo('selected="selected"'); ?> value="SD">South Dakota</option>
                                            <option <?php if($patientInfo->state === "TN") echo('selected="selected"'); ?> value="TN">Tennessee</option>
                                            <option <?php if($patientInfo->state === "TX") echo('selected="selected"'); ?> value="TX">Texas</option>
                                            <option <?php if($patientInfo->state === "UT") echo('selected="selected"'); ?> value="UT">Utah</option>
                                            <option <?php if($patientInfo->state === "VT") echo('selected="selected"'); ?> value="VT">Vermont</option>
                                            <option <?php if($patientInfo->state === "VA") echo('selected="selected"'); ?> value="VA">Virginia</option>
                                            <option <?php if($patientInfo->state === "WA") echo('selected="selected"'); ?> value="WA">Washington</option>
                                            <option <?php if($patientInfo->state === "WV") echo('selected="selected"'); ?> value="WV">West Virginia</option>
                                            <option <?php if($patientInfo->state === "WI") echo('selected="selected"'); ?> value="WI">Wisconsin</option>
                                            <option <?php if($patientInfo->state === "WY") echo('selected="selected"'); ?> value="WY">Wyoming</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="profileTextTitleCol"><span class="profileTextTitle">Zip</span></td>
                                    <td><span class="profileTextData"><?php echo($patientInfo->zip); ?></span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if($strWR!='') { ?> 
                        <a id="linkWR">
                            <img id="imgLinkWR" src="images/flat_clock.png" />
                            <label id="lblLinkWR">Go to Dr. Howser Waiting room</label>
                        </a>
                        <?php } ?> 
                </div>
            </div>
            <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
            <script type="text/javascript" src="/js/jQueryFileUpload/js/vendor/jquery.ui.widget.js"></script>
            <script type="text/javascript" src="/js/jQueryFileUpload/js/jquery.iframe-transport.js"></script>
            <script type="text/javascript" src="/js/jQueryFileUpload/js/jquery.fileupload.js"></script>
            <script type="text/javascript" src="<?php autoVerCss('/js/patient_main.js');?>"></script>
            <script type="text/javascript" src="/js/sweetalert.min.js"></script>
            <?php //include 'video_chat.php';?>
        <?php } else { ?>
            <p><span class="error">You are not authorized to access this page.</span> Please <a target="self" href="/main.php">login</a>.</p>
        <?php } ?>
    </body>
</html>

