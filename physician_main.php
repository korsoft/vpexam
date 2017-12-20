<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';
    sec_session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
    $_strPageTitle   = 'VPExam - Physician Main Page';
    $_strHeaderTitle = 'MY PATIENTS';
    $_arrStyles[]    = '/style/physician_main.css';
    $_arrStyles[]    = '/style/single_waiting_room.css';
    $_arrScripts[]   = '/js/physician_main.js';
    $_arrScripts[]   = '/js/waiting_room.js';
    $_arrScripts[]   = '/js/single_waiting_room.js';


    $numPage     = intval(empty($_GET['page'])?0:$_GET['page'] );
    $numPage     = $numPage<0?0:$numPage; 
    $numLimit    = 20;
    $numOffSet   = ($numPage)*$numLimit; 
    $blnHasNext  = false;
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';
?>
<script>
    var _numPhysicianId= <?php echo $_SESSION['user_id']; ?>;
</script>
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
            <div class="mainContent PatientsWaiting">
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
                                $fwaitingroom = $info->waitingroom;

                                $col1 =
                                    "<td class=\"shortColumn patientsTable\" id=\"$id\">
                                        <img class=\"patientProfilePic\" src=\"includes/getProfileImage.php?id=$id&type=1\">
                                        <div class=\"nameMRNDiv\">
                                            <div style=\"margin: 20px 0 0 0;\">
                                                <div>$fname</div>
                                                <div>$mrn</div>
                                            </div>
                                        </div>
                                    </td>
                                    ";

                                $col2 = "<td class=\"shortColumn patientsTable\" id=\"$id\">$lname</td>";
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
                                    "<td class=\"longColumn patientsTable\" id=\"$id\">
                                        <div class=\"nameMRNDiv\">
                                            <div style=\"margin: 20px 0 0 0;\">
                                                <div>$fullAddr</div>
                                                <div>$phAndType</div>
                                            </div>
                                        </div>
                                    </td>
                                    ";
                                if($fwaitingroom == "yes") {
                                    $retVal ='<img src="/img/video-camera4.png">';
                                } else{
                                    $retVal='';
                                };
                                $col6 =
                                    "<td>
                                        <div class=\"waitingroom\" data-id=\"$id\" data-name=\"lala\" onclick=\"openWRChat( this );\">
                                            <div class=\"waitingPatientInner\"> $retVal</div>
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
                                <tr class=\"hoverableRow patientsTable2\" id=\"$id\">
                                    $col1
                                    $col2
                                    $col3
                                    $col4
                                    $col6
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
            <div id="divSidenavPhys" class="dvsidenavPhys" >
                <div id="divToolPhys">
                    <img id="imgToolPhys"  src="images/tool.png"/>
                </div>  
                <a href="javascript: void(0);"   id="lnBandwidth"><img id="imgbandwidth" src="images/bw_black.png"/>&nbsp;&nbsp;Bandwidth</a>
                <a href="#" onclick="return false;"><img id="imgCamera" src="images/camera_black.png"/>&nbsp;&nbsp;Camera</a>
                <a href="#" onclick="return false;"><img id="imgMic"   src="images/mic_black.png"/>&nbsp;&nbsp;&nbsp;Microphone</a>
                <a href="javascript: void(0);" id="lnSound"><img id="imgSound" src="images/audio_icon.png"/>&nbsp;&nbsp;Sound</a>
            </div>
            <div id="modalSoundTest"  class="hide" ></div>
            <div id="messageSoundTest" class="hide">
                <a href="javascript:void(0);" onClick="SoundTest.hide();return false;">X</a>
                <br />
                <br />
                <br />
                <div id="dlgTestSoundDialog" title="Sound Test">
                    <p style="margin: 0 0 1px 0;">Can you hear the sound?</p>
                    <br/>
                    <audio id="audSoundTest" controls="controls" controlsList="nodownload">
                        <source src="sounds/bird_tweet.mp3" />
                    </audio>
                    <br/>
                    <br/>
                    <div class="button-dark" id="btnYes" onclick="fncChangeImg(true);" >Yes</div>&nbsp;
                    <div class="button-dark" id="btnNo"  onclick="fncChangeImg(false);" >No</div>
                    <br/>
                </div>
                <br/>
            </div>
<?php
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/footer_physician.php';
    include_once $_SERVER['DOCUMENT_ROOT'] .'/video_chat.php';
