<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';
    sec_session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
    $_strPageTitle   = 'Physician Settings';
    $_strHeaderTitle = 'MY SETTINGS';
    $_arrStyles[]    = '/style/physician_settings.css';
    $_arrStyles[]    = 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css';
    $_arrStyles[]    = '/js/cropper/cropper.css';
    $_arrStyles[]    = '/style/checkboxes.css';
    $_arrStyles[]    = '/js/qtip2/jquery.qtip.min.css';


    $_arrScripts[]   = 'https://code.jquery.com/ui/1.11.4/jquery-ui.js';
    $_arrScripts[]   = 'https://tinymce.cachefly.net/4.1/tinymce.min.js';
    $_arrScripts[]   = '/js/sha512.js';
    $_arrScripts[]   = '/js/physician_settings.js';
    $_arrScripts[]   = '/js/cropper/cropper.min.js';
    $_arrScripts[]   = '/js/filereader.js';
    $_arrScripts[]   = '/js/jquery.ajax-progress.js';
    $_arrScripts[]   = '/js/jquery-easy-tabs/lib/jquery.easytabs.js';
    $_arrScripts[]   = '/js/spin.js';
    $_arrScripts[]   = '/js/jquery.spin.js';
    $_arrScripts[]   = '/js/pdf.js/pdf.js';
    $_arrScripts[]   = '/js/numeric/jquery.numeric.js';
    $_arrScripts[]   = '/js/qtip2/jquery.qtip.min.js';
    $_arrScripts[]   = '/js/waiting_room.js';
    
    
    $physId                 = $_SESSION['user_id'];
    $physInfo               = getExtendedPhysicianInfo($physId, $mysqli);
    $maxStethRecordTime     = getMaxStethRecordTime($mysqli, $physId)['data']['max_steth_record_time'];
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';
    //
?>   

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!--link rel="stylesheet" href="/resources/demos/style.css"-->
    <style>
        #sortable { list-style-type: none; margin: 0; padding: 0;}
        #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; position: relative; }
        #sortable li span { position: absolute; margin-left: -1.3em; }
    </style>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    

                <div class="mainContent">
                    <div class="container">
                        <div class="left">
                            <div class="smallProfileDiv">
                                <img id="profilePic" src="includes/getProfileImage.php?id=<?php echo($physInfo->physicianId); ?>&type=2">
                                <div>
                                    <span class="infoText infoTextName"><?php echo($physInfo->firstName . ' ' . $physInfo->lastName) ?></span>
                                    <span class="infoText"><?php echo($physInfo->npi); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="right">
                            <div class="tab-container" >
                                <ul class="etabs">
                                    <li class="tab"><a href="#tabSettings">Settings</a></li>
                                    <li class="tab"><a href="#tabBAA">BAA</a></li>
                                </ul>
                                <div class="panel-container">
                                    <div id="tabSettings">
                                        <div class="settingsDiv">
                                            <div class="subSettingsDiv">
                                                <span class="subHeader">Profile Picture</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div class="button-dark" id="btnChangeProfilePic">Change Profile Picture</div>
                                                    <div class="changeProfilePicDialog" title="Change Profile Picture">
                                                        <div class="outerDropContainer">
                                                            <p style="margin: 0 0 10px 0;">Click the button below to select an image to upload or drag and drop one.<br />
                                                                You will then have the chance to crop the image how you would like.<br /><br />
                                                                Note: Large images may take a few seconds to load
                                                            </p>
                                                            <div class="dropContainer">
                                                                <span class="dropContainerText">Click Here To Choose An Image</span><br />
                                                                <span class="dropContainerText">OR</span><br />
                                                                <span class="dropContainerText">Drag And Drop An Image Here</span>
                                                            </div>
                                                        </div>
                                                        <input accept="image/*" type="file" id="fileChooser">
                                                        <div class="cropBoxOuterContainer">
                                                            <div class="cropBoxContainer">
                                                                <img src="">
                                                            </div>
                                                            <div class="cropBoxControlsContainer">
                                                                <div style="display: inline-block; margin: 0 0 0 2px;">
                                                                    <div class="img-button" id="btnZoomIn">
                                                                        <img src="img/zoom_in.png">
                                                                    </div>
                                                                    <div class="img-button" id="btnZoomOut">
                                                                        <img src="img/zoom_out.png">
                                                                    </div>
                                                                    <div class="img-button" id="btnRotateCCW">
                                                                        <img src="img/rotate_ccw.png">
                                                                    </div>
                                                                    <div class="img-button" id="btnRotateCW">
                                                                        <img src="img/rotate_cw.png">
                                                                    </div>
                                                                </div>
                                                                <div style="display: inline-block; float: right;">
                                                                    <div class="text-button" id="btnNewImage">
                                                                        <span>New Image</span>
                                                                    </div>
                                                                    <div id="btnCrop" class="img-text-button" style="margin: 0 0 0 20px;">
                                                                        <img src="img/crop.png">
                                                                        <span>Crop Image</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="loadingContainer">
                                                            <span>Please wait while your image is being uploaded and cropped.</span>
                                                            <div id="uploadProgress"><div class="progress-label">Uploading...</div></div>
                                                        </div>
                                                        <div class="errorContainer">
                                                            <span>An error occurred while uploading and cropping the selected image.</span>
                                                            <span id="errorCode"></span>
                                                            <span>If this error persists, please contact the <a href="mailto:webmaster@vpexam.com">site administrator</a>.</span>
                                                        </div>
                                                        <div class="resultContainer">
                                                            <div class="cropResultDiv">
                                                                <img id="cropResult" src="">
                                                            </div>
                                                            <div style="float: right; margin: 5px 0 0 0;">
                                                                <div class="button-dark" id="btnResultOk">Ok</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv">
                                                <span class="subHeader">Turn on/off the waiting room email notifications </span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div id="turnEmailnotification">
                                                        <input type="radio" name="emailnotification" value="1" <?php if($physInfo->email_notification == 1){?> checked<?php }?>> Yes &nbsp; &nbsp;
                                                        <input type="radio" name="emailnotification" value="0" <?php if($physInfo->email_notification == 0){?> checked<?php }?>> No &nbsp; &nbsp;
                                                        <div class="button-dark-smaller" id="btnEmailNot" style="display: inline; margin: 0 0 0 10px;">Save</div><label id="success_msgEN">Saved successfully</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv">
                                                <span class="subHeader">Password</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div id="changePasswordDiv">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="titleTdPwd">Enter Current Password</td>
                                                                    <td><input class="holo" id="inputCurrentPassword" type="password"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="titleTdPwd">Enter New Password</td>
                                                                    <td>
                                                                        <input class="holo" id="inputNewPassword" type="password">
                                                                        <div class="hidden" id="qtipContent" style="display: none">
                                                                            <table>
                                                                                <tr>
                                                                                    <td>Must contain at least 8 characters</td>
                                                                                    <td><img id="qtImg1" height="15" width="15" src="img/red_x.png"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Must contain at least 1 uppercase letter</td>
                                                                                    <td><img id="qtImg2" height="15" width="15" src="img/red_x.png"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Must contain at least 1 lowercase letter</td>
                                                                                    <td><img id="qtImg3" height="15" width="15" src="img/red_x.png"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Must contain at least 1 number</td>
                                                                                    <td><img id="qtImg4" height="15" width="15" src="img/red_x.png"></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="titleTdPwd">Confirm New Password</td>
                                                                    <td><input class="holo" id="inputConfirmNewPassword" type="password"></td>
                                                                    <td><img id="imgConfirmPwd" height="20" width="20" src="img/red_x.png" style="margin-left: 20px; display: none;"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="button-dark" id="btnChangePassword">Change Password</div>
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv">
                                                <span class="subHeader">Waiting Room</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div>
                                                        <div>
                                                        <label style="font-size: 14px; margin: 0 0 0 10px;">Edit Waiting Room URL</label>
                                                            <label for="inputWRUrl" style="font-size: 12px; font-weight: bold; margin: 0 0 0 10px;">https://vpexam.com/</label><input class="holo" style="display: inline;" maxlength="100" type="text" id="inputWRUrl" value="<?php echo($physInfo->username); ?>">
                                                            <div class="button-dark-smaller" id="btnWRUrl" style="display: inline; margin: 0 0 0 10px;">Save</div><label id="success_msgWR">Saved successfully</label>
                                                        </div>
                                                    </div>  
                                                    <div class="hr"></div> 
                                                    <div>
                                                        <div style="margin: 0 0 10px 0;">
                                                            <label style="font-size: 14px; margin: 0 0 0 10px;">Change Waiting Room Background Image</label>
                                                        </div> 
                                                        <div class="smallPreviewWRDiv">
                                                            <div><img id="BackgroundImgWR" src="includes/getProfileImage.php?id=<?php echo($physInfo->physicianId); ?>&type=3"></div>
                                                            <div class="button-dark" id="btnChangeProfileWRI">Change Image</div>
                                                        </div> 
                                                    </div>                                           
                                                    <div class="changeProfilePicDialogWRI" title="Change Waiting Room Background Image">
                                                        <div class="outerDropContainerWRI">
                                                            <p style="margin: 0 0 10px 0;">Click the button below to select an image to upload or drag and drop one.<br />
                                                                You will then have the chance to crop the image how you would like.<br /><br />
                                                                Note: Large images may take a few seconds to load
                                                            </p>
                                                            <div class="dropContainerWRI">
                                                                <span class="dropContainerTextWRI">Click Here To Choose An Image</span><br />
                                                                <span class="dropContainerTextWRI">OR</span><br />
                                                                <span class="dropContainerTextWRI">Drag And Drop An Image Here</span>
                                                            </div>
                                                        </div>
                                                        <input accept="image/*" type="file" id="fileChooserWRI">
                                                        <div class="cropBoxOuterContainerWRI">
                                                            <div class="cropBoxContainerWRI">
                                                                <img src="">
                                                            </div>
                                                            <div class="cropBoxControlsContainerWRI">
                                                                <div style="display: inline-block; margin: 0 0 0 2px;">
                                                                    <div class="img-button" id="btnZoomInWRI">
                                                                        <img src="img/zoom_in.png">
                                                                    </div>
                                                                    <div class="img-button" id="btnZoomOutWRI">
                                                                        <img src="img/zoom_out.png">
                                                                    </div>
                                                                    <div class="img-button" id="btnRotateCCWWRI">
                                                                        <img src="img/rotate_ccw.png">
                                                                    </div>
                                                                    <div class="img-button" id="btnRotateCWWRI">
                                                                        <img src="img/rotate_cw.png">
                                                                    </div>
                                                                </div>
                                                                <div style="display: inline-block; float: right;">
                                                                    <div class="text-button" id="btnNewImageWRI">
                                                                        <span>New Image</span>
                                                                    </div>
                                                                    <div id="btnCropWRI" class="img-text-button" style="margin: 0 0 0 20px;">
                                                                        <img src="img/crop.png">
                                                                        <span>Crop Image</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="loadingContainerWRI">
                                                            <span>Please wait while your image is being uploaded and cropped.</span>
                                                            <div id="uploadProgressWRI"><div class="progress-labelWRI">Uploading...</div></div>
                                                        </div>
                                                        <div class="errorContainerWRI">
                                                            <span>An error occurred while uploading and cropping the selected image.</span>
                                                            <span id="errorCodeWRI"></span>
                                                            <span>If this error persists, please contact the <a href="mailto:webmaster@vpexam.com">site administrator</a>.</span>
                                                        </div>
                                                        <div class="resultContainerWRI">
                                                            <div class="cropResultDivWRI">
                                                                <img id="cropResultWRI" src="">
                                                            </div>
                                                            <div style="float: right; margin: 5px 0 0 0;">
                                                                <div class="button-dark" id="btnResultOkWRI">Ok</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv">
                                                <span class="subHeader">My Practice Address</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div id="practiceAddressDiv">
                                                        <?php
                                                        if ($physInfo->practiceName !== "")
                                                            echo('<span class="normalTextBlock" id="practiceName">' . $physInfo->practiceName . '</span>');
                                                        ?>
                                                        <span class="normalTextBlock" id="practiceAddr"><?php echo($physInfo->practiceAddr); ?></span>
                                                        <span class="normalTextBlock" id="practiceCityStateZip"><?php echo($physInfo->practiceCity . ', ' . $physInfo->practiceState . ' ' . $physInfo->practiceZip); ?></span>
                                                    </div>
                                                    <div id="practiceAddressDivEdit">
                                                        <table>
                                                            <tbody>
                                                            <tr>
                                                                <td class="titleTd">Practice Name</td>
                                                                <td><input class="holo" id="inputPracticeName" type="text" value="<?php echo($physInfo->practiceName); ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="titleTd">Address</td>
                                                                <td><input class="holo" id="inputPracticeAddr" type="text" value="<?php echo($physInfo->practiceAddr); ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="titleTd">City</td>
                                                                <td><input class="holo" id="inputPracticeCity" type="text" value="<?php echo($physInfo->practiceCity); ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="titleTd">State</td>
                                                                <td>
                                                                    <select class="holo" id="selPracticeState" name="state">
                                                                        <option <?php if ($physInfo->practiceState === "") echo('selected="selected"'); ?> value="SEL">Select State:</option>
                                                                        <option value="AL">Alabama</option>
                                                                        <option value="AK">Alaska</option>
                                                                        <option value="AZ">Arizona</option>
                                                                        <option value="AR">Arkansas</option>
                                                                        <option value="CA">California</option>
                                                                        <option value="CO">Colorado</option>
                                                                        <option value="CT">Connecticut</option>
                                                                        <option value="DE">Delaware</option>
                                                                        <option value="DC">District Of Columbia</option>
                                                                        <option value="FL">Florida</option>
                                                                        <option value="GA">Georgia</option>
                                                                        <option value="HI">Hawaii</option>
                                                                        <option value="ID">Idaho</option>
                                                                        <option value="IL">Illinois</option>
                                                                        <option value="IN">Indiana</option>
                                                                        <option value="IA">Iowa</option>
                                                                        <option value="KS">Kansas</option>
                                                                        <option value="KY">Kentucky</option>
                                                                        <option value="LA">Louisiana</option>
                                                                        <option value="ME">Maine</option>
                                                                        <option value="MD">Maryland</option>
                                                                        <option value="MA">Massachusetts</option>
                                                                        <option value="MI">Michigan</option>
                                                                        <option value="MN">Minnesota</option>
                                                                        <option value="MS">Mississippi</option>
                                                                        <option value="MO">Missouri</option>
                                                                        <option value="MT">Montana</option>
                                                                        <option value="NE">Nebraska</option>
                                                                        <option value="NV">Nevada</option>
                                                                        <option value="NH">New Hampshire</option>
                                                                        <option value="NJ">New Jersey</option>
                                                                        <option value="NM">New Mexico</option>
                                                                        <option value="NY">New York</option>
                                                                        <option value="NC">North Carolina</option>
                                                                        <option value="ND">North Dakota</option>
                                                                        <option value="OH">Ohio</option>
                                                                        <option value="OK">Oklahoma</option>
                                                                        <option value="OR">Oregon</option>
                                                                        <option value="PA">Pennsylvania</option>
                                                                        <option value="RI">Rhode Island</option>
                                                                        <option value="SC">South Carolina</option>
                                                                        <option value="SD">South Dakota</option>
                                                                        <option value="TN">Tennessee</option>
                                                                        <option value="TX">Texas</option>
                                                                        <option value="UT">Utah</option>
                                                                        <option value="VT">Vermont</option>
                                                                        <option value="VA">Virginia</option>
                                                                        <option value="WA">Washington</option>
                                                                        <option value="WV">West Virginia</option>
                                                                        <option value="WI">Wisconsin</option>
                                                                        <option value="WY">Wyoming</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="titleTd">Zip</td>
                                                                <td><input class="holo" id="inputPracticeZip" type="text" value="<?php echo($physInfo->practiceZip); ?>"></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        <div style="margin: 10px 0 0 0;">
                                                            <div class="button-dark-smaller" id="btnSavePracticeAddr">Save</div>
                                                            <div class="button-dark-smaller" id="btnCancelPracticeAddr">Cancel</div>
                                                        </div>
                                                    </div>
                                                    <div class="button-dark-smaller" id="btnEditPracticeAddr">Edit</div>
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv">
                                                <span class="subHeader">My Normal Exam</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div class="clipboardContainer">
                                                        <textarea id="taNormalText"></textarea>
                                                        <div style="display: block; height: 30px;">
                                                            <div style="float: left; margin: 5px 0 0 0;">
                                                                <span id="normalLastSaved"></span>
                                                            </div>
                                                            <div style="float: right; margin: 5px 0 0 0;">
                                                                <div class="button-dark-smaller" id="btnSaveNormal">Save</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv" style="margin: 20px 0 0 0;">
                                                <span class="subHeader">My Physical Exam Components</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div class="button-dark" id="btnSetExamComponents">Set Physical Exam Components</div>
                                                    <div class="button-dark" id="btnCreateExamComponents">Create Custom Physical Exam Components</div>
                                                    <div id="setExamComponentsDlg" title="Set Exam Components" ></div>
                                                    <div id="dlgConfirmDeleteExCom" title="Delete Exam Component?">
                                                       <p>
                                                           <img src="caution.png" width="30" height="30" style="display: inline; float:left; margin:0 7px 20px 0;" />
                                                           Once your Exam Component "<label id="lblExamComponent"></label>" is deleted it cannot be recovered and will be removed from other physicians who have selected it. Are you sure you want to delete it?
                                                       </p>
                                                    </div>
                                                    </div>
                                                    <div class="innerSettingsDiv">
                                                    <div id="createExamComponentsDlg" title="Create Exam Component">
                                                        <table >
                                                                    <tbody>
                                                                    <tr>
                                                                        <td class="titleTd">Title</td>
                                                                        <td><input class="holo" id="inputComponentTitle" type="text" value=""></td>
                                                                        <td rowspan="5" style="text-align: center; vertical-align: bottom;padding: 5px  " >
                                                                            <div id="contentvidMaleModel">
                                                                                <video id="vidMaleModel" width="200" height="170" controls poster="/images/player_poster.jpg" preload="metadata">
                                                                                </video>
                                                                            </div>
                                                                            <img id="imgMaleModel" src="" alt="" />
                                                                            <form action="/includes/upload_exam_component_image_audio.php" id="myFormMale" name="myFormMale" method="post" enctype="multipart/form-data">
                                                                                <input type="file" name="fileToUploadMale" id="fileToUploadMale" style="display: none;" >
                                                                                <div class="button-dark" id="btnChangeImgMale" >3D model - Male</div>
                                                                            </form>
                                                                        </td>
                                                                        <td rowspan="5" style="text-align: center; vertical-align: bottom;padding: 5px " >
                                                                            <div id="contentvidFemaleModel">
                                                                                <video id="vidFemaleModel" width="200" height="170" controls poster="/images/player_poster.jpg" preload="metadata">
                                                                                </video>
                                                                            </div>
                                                                            <img id="imgFemaleModel" src="" alt="" />
                                                                            <form action="/includes/upload_exam_component_image_audio.php" id="myFormFemale" name="frmuploadFemale" method="post" enctype="multipart/form-data">
                                                                                <input type="file" name="fileToUploadFemale" id="fileToUploadFemale" style="display: none;">
                                                                                <div class="button-dark" id="btnChangeImgFemale">3D model - Female</div>
                                                                            </form>
                                                                        </td>
                                                                        <td rowspan="5" style="text-align: center; vertical-align: bottom;padding: 5px  " >
                                                                            <audio  controls id="sndAudio" name="sndAudio"  style="display: none;">
                                                                                    Your browser does not support the audio element.
                                                                            </audio>
                                                                            <form action="/includes/upload_exam_component_image_audio.php" id="myFormAudio" name="frmuploadAudio" method="post" enctype="multipart/form-data">
                                                                                <input type="file" name="fileToUploadAudio" id="fileToUploadAudio" style="display: none;">
                                                                                <div class="button-dark" id="btnChangeAudio">Audio instructions</div>
                                                                            </form>
                                                                        </td>                                                                   
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="titleTd">Abbrev</td>
                                                                        <td><input class="holo" id="inputComponentAbbrev" maxlength="10" type="text" value=""></td>
                                                                    </tr>  
                                                                    <tr>
                                                                        <td class="titleTd">Description</td>
                                                                        <td><input class="holo" id="inputComponentDesc" type="text" value=""></td>
                                                                    </tr>                                                                      
                                                                    <tr>
                                                                        <td class="titleTd">Type</td>
                                                                        <td>
                                                                            <select name="cmdComponentType" id="cmdComponentType">
                                                                                <option value="a">Audio</option>
                                                                                <option value="v">Video</option>
                                                                            </select>
                                                                        </td>
                                                                    </tr>                                                                    
                                                                    <tr>
                                                                        <td><input type="radio" id="rdComponentPublicPb"   name="rdComponentPublic" value="1"  checked="checked">
                                                                            <label for="rdComponentPublicPb">Public</label></td>
                                                                       <td>
                                                                            <input type="radio" id="rdComponentPublicPv"
                                                                                 name="rdComponentPublic" value="0">
                                                                            <label for="rdComponentPublicPv">Private</label>                                                                            
                                                                        </td>
                                                                    </tr>                                                                    
                                                                    </tbody>
                                                                </table>
                                                        <div style="margin: 10px 0 0 0;">
                                                            <div class="button-dark-smaller" id="btnSaveCreateComponent">Save</div>
                                                            <div class="button-dark-smaller" id="btnCancelCreateComponent">Cancel</div><label id="success_msgEC">Saved successfully</label>
                                                        </div>
                                                    </div>                                                    
                                                </div>
                                            </div>
                                            <div class="subSettingsDiv" style="margin: 20px 0 0 0;">
                                                <span class="subHeader">Max Stethoscope Recording Length</span>
                                                <div class="hr"></div>
                                                <div class="innerSettingsDiv">
                                                    <div>
                                                        <div>
                                                            <input class="holo" style="display: inline; text-align: right;" type="text" id="inputMaxTime" <?php if ($maxStethRecordTime > 0) echo('value="' . $maxStethRecordTime . '"'); ?>>
                                                            <label for="inputMaxTime" style="font-size: 14px; margin: 0 0 0 10px;">(in seconds)</label>
                                                            <div class="button-dark-smaller" id="btnSaveMaxTime" style="display: inline; margin: 0 0 0 10px;">Save</div><label id="success_msg">Saved successfully</label>
                                                        </div>
                                                        <div style="margin: 10px 0 0 0;">
                                                            <input class="holo" type="checkbox" id="cbMaxTimeUnlimited" <?php if ($maxStethRecordTime == -1 || $maxStethRecordTime == 0) echo("checked"); ?>>
                                                            <label for="cbMaxTimeUnlimited" style="font-size: 14px; margin: 0 0 0 5px;">Unlimited</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tabBAA">
                                        <h3>Business Associate Agreement</h3>
                                        <div id="baaPDFContainer" style="display: none;">
                                            <div>
                                                <div style="background: gray; height: 4px; width: 605px;"></div>
                                                <div id="pdfContainer" style="background: gray; overflow-y: auto; max-height: 700px; width: 620px; text-align: center;">

                                                </div>
                                                <div style="background: gray; height: 4px; width: 605px;"></div>
                                            </div>
                                            <div style="margin-top: 10px; width: 605px;">
                                                <div style="float: right;">
                                                    <div class="button-dark button-red" id="btnDeleteBAA" style="margin-right: 10px;">Delete BAA</div>
                                                    <div class="button-dark" id="btnDownloadBAA">Download BAA</div>
                                                </div>
                                            </div>
                                            <div id="dlgConfirmDeleteBAA" title="Delete BAA?">
                                                <p>
                                                    <img src="caution.png" width="30" height="30" style="display: inline; float:left; margin:0 7px 20px 0;" />
                                                    Once your BAA is deleted it cannot be recovered. Are you sure you want to delete you BAA?
                                                </p>
                                            </div>
                                        </div>
                                        <div id="baaInfoContainer">
                                            <p id="baaNeedInfoText">We need a few more pieces of information in order to generate a Business Associate agreement (BAA) with vpexam.com.
                                                Please provide the following information.
                                            </p>
                                            <div id="noBAAContainer">
                                                <input type="checkbox" id="cbNoBAA">
                                                <label for="cbNoBAA">I do not require a Business Associates Agreement</label>
                                            </div>
                                            <div id="baaOverlay"></div>
                                            <table id="baaSettingsTable">
                                                <tbody>
                                                <tr>
                                                    <td class="baaTitleTd">Your Name</td>
                                                    <?php $physName = $physInfo->firstName . ($physInfo->middleName === "" ? "" : " " . $physInfo->middleName) . " " . $physInfo->lastName; ?>
                                                    <td><input class="holo" id="inputBAAName" type="text" value="<?php echo($physName); ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">National Provider Identifier</td>
                                                    <td><input class="holo" id="inputBAANPI" type="text" value="<?php echo($physInfo->npi); ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">Name of Covered Entity</td>
                                                    <td><input class="holo" id="inputBAACoveredEntity" type="text"></td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">State</td>
                                                    <td>
                                                        <select class="holo" id="selBAAState" name="state">
                                                            <option value="SEL" selected="selected">Select State:</option>
                                                            <option value="Alabama">Alabama</option>
                                                            <option value="Alaska">Alaska</option>
                                                            <option value="Arizona">Arizona</option>
                                                            <option value="Arkansas">Arkansas</option>
                                                            <option value="California">California</option>
                                                            <option value="Colorado">Colorado</option>
                                                            <option value="Connecticut">Connecticut</option>
                                                            <option value="Delaware">Delaware</option>
                                                            <option value="District of Columbia">District Of Columbia</option>
                                                            <option value="Florida">Florida</option>
                                                            <option value="Georgia">Georgia</option>
                                                            <option value="Hawaii">Hawaii</option>
                                                            <option value="Idaho">Idaho</option>
                                                            <option value="Illinois">Illinois</option>
                                                            <option value="Indiana">Indiana</option>
                                                            <option value="Iowa">Iowa</option>
                                                            <option value="Kansas">Kansas</option>
                                                            <option value="Kentucky">Kentucky</option>
                                                            <option value="Louisiana">Louisiana</option>
                                                            <option value="Maine">Maine</option>
                                                            <option value="Maryland">Maryland</option>
                                                            <option value="Massachusetts">Massachusetts</option>
                                                            <option value="Michigan">Michigan</option>
                                                            <option value="Minnesota">Minnesota</option>
                                                            <option value="Mississippi">Mississippi</option>
                                                            <option value="Missouri">Missouri</option>
                                                            <option value="Montana">Montana</option>
                                                            <option value="Nebraska">Nebraska</option>
                                                            <option value="Nevada">Nevada</option>
                                                            <option value="New Hampshire">New Hampshire</option>
                                                            <option value="New Jersey">New Jersey</option>
                                                            <option value="New Mexico">New Mexico</option>
                                                            <option value="New York">New York</option>
                                                            <option value="North Carolina">North Carolina</option>
                                                            <option value="North Dakota">North Dakota</option>
                                                            <option value="Ohio">Ohio</option>
                                                            <option value="Oklahoma">Oklahoma</option>
                                                            <option value="Oregon">Oregon</option>
                                                            <option value="Pennsylvania">Pennsylvania</option>
                                                            <option value="Rhode Island">Rhode Island</option>
                                                            <option value="South Carolina">South Carolina</option>
                                                            <option value="South Dakota">South Dakota</option>
                                                            <option value="Tennessee">Tennessee</option>
                                                            <option value="Texas">Texas</option>
                                                            <option value="Utah">Utah</option>
                                                            <option value="Vermont">Vermont</option>
                                                            <option value="Virginia">Virginia</option>
                                                            <option value="Washington">Washington</option>
                                                            <option value="West Virginia">West Virginia</option>
                                                            <option value="Wisconsin">Wisconsin</option>
                                                            <option value="Wyoming">Wyoming</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">Business Type</td>
                                                    <td>
                                                        <select class="holo" id="selBAABusinessType" name="businessType">
                                                            <option selected="selected" value="SEL">Select Business Type:</option>
                                                            <option value="Professional Corporation">Professional Corporation</option>
                                                            <option value="Partnership">Partnership</option>
                                                            <option value="Sole Proprietorship">Sole Proprietorship</option>
                                                            <option value="Non Profit">Non Profit</option>
                                                            <option value="O">Other</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr id="trBAAOtherBusinessType" style="display: none;">
                                                    <td class="baaTitleTd">Other Business Type</td>
                                                    <td><input class="holo" id="inputBAAOtherBusinessType" type="text"></td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">Type of Organization</td>
                                                    <td><input class="holo" id="inputBAAOrgType" type="text"></td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">Address</td>
                                                    <td><input class="holo" id="inputBAAAddr" type="text""></td>
                                                </tr>
                                                <tr>
                                                    <td class="baaTitleTd">Your Position or Title</td>
                                                    <td><input class="holo" id="inputBAATitle" type="text""></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td style="float: right; margin: 20px 0 0 0;">
                                                        <div class="button-dark" id="btnGenerateBAA">Generate BAA</div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="spin"></div>
<?php
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/footer_physician.php';
