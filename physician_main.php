<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';
    sec_session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
    $_strPageTitle   = 'VPExam - Physician Main Page';
    $_strHeaderTitle = 'MY PATIENTS';
    $_arrStyles[]    = '/style/physician_main.css';
    $_arrStyles[]    = '/style/single_waiting_room.css';
    $_arrStyles[]    = '/style/jquery.dataTables.min.css';
    $_arrScripts[]   = '/js/physician_main.js';
    $_arrScripts[]   = '/js/waiting_room.js';
    $_arrScripts[]   = '/js/jquery.dataTables.min.js';


    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';
?>
<script src="https://code.jquery.com/jquery-latest.js" type="text/javascript"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js" type="text/javascript"></script>
<script>
    var _numPhysicianId= <?php echo $_SESSION['user_id']; ?>;

</script>
            <div class="mainContent PatientsWaiting">
                <table id="example" class="tableContent" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Date of birth</th>
                            <th>Gender</th>
                            <th>Vpexam uploaded</th>
                            <th>Waiting room</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="divSidenavPhys" class="dvsidenavPhys" >
                <div id="divToolPhys">
                    <img id="imgToolPhys"  src="images/tool.png"/>
                </div>  
                <a href="javascript: void(0);"   id="lnBandwidth"><img id="imgbandwidth" src="images/wifi.png" class="normal"/>&nbsp;&nbsp;Bandwidth</a>
                <a href="#" onclick="return false;"><img id="imgCamera" src="images/webcam.png"/>&nbsp;&nbsp;Camera</a>
                <a href="#" onclick="return false;"><img id="imgMic"   src="images/micro.png"/>&nbsp;&nbsp;&nbsp;Microphone</a>
                <a href="javascript: void(0);" id="lnSound"><img id="imgSound"  src="images/sound.png"/>&nbsp;&nbsp;Sound</a>
            </div>
            <div id="modalSoundTest"  style="display: none"></div>
            <div id="messageSoundTest" style="display: none">
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
?>
