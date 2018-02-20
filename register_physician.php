<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/register_physician.css');?>">
    <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/holoInputs.css');?>">
    <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/strength.css');?>">
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
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
    <!--<script src='https://www.google.com/recaptcha/api.js'></script>-->
    <script src="https://code.jquery.com/jquery-latest.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script src="<?php autoVerCss('/js/sha512.js');?>"></script>
    <script src="js/jquery.inputmask/inputmask.js"></script>
    <script src="js/jquery.inputmask/jquery.inputmask.js"></script>
    <script src="js/numeric/jquery.numeric.js"></script>
    <script src="<?php autoVerCss('/js/register_physician.js');?>"></script>
    <script src="<?php autoVerCss('/js/bloomjs/src/bloom.js');?>"></script>
    <style>
        /* Document-level CSS needed to override jQuery UI default CSS style for font color of tooltip */
        .ui-tooltip {
            color: red;
        }

        #titlePwd {
            vertical-align: top;
        }
    </style>
    <title>VPExam - Register</title>
</head>

<body>
<div id="header">
    <div id="logo">
        <div id="logo_text">
            <img src="img/logo_img.png" />
        </div>
        <div class="topmenu">
            <div class="topmenuItem hoverable" id="btnTopHome">
                <a href="main.php"><span>Home</span></a>
            </div>
            <!--<span class="topmenuItem split">|</span>-->
            <div class="topmenuItem hoverable" id="btnTopLogin">
                <span>Login</span>
            </div>
            <div id="sidebarLoginPhysician">
                <div class="arrow-up"></div>
                <div>
                    <div class="sidebar_item">
                        <h3>Login</h3>
                        <form class="loginForm" method="post" action="includes/process_login.php">
                            <label for="user" class="loginW">Username:</label><br>
                            <input class="login" name="username" id="user" type="text"><br>
                            <br>
                            <label for="user" class="loginW">Password:</label><br>
                            <input class="login" name="password" id="password" type="password"><br>
                            <br>
                            <input name="remember" type="checkbox"><label for="remember" class="loginW">Remember
                                Me</label><br>
                            <br>
                            <input onclick="formhash(this.form, this.form.password);" name="btnLogin"
                                   class="button" value="Login" type="button">
                            <input class="buttonRequestTrial" type="button" value="Request Free Trial" >
                        </form>
                        <br>
                    </div>
                </div>
            </div>
            <div class="topmenuItem hoverable" id="btnTopContact">
                <span>Contact Us</span>
            </div>
        </div>
    </div>
</div>
<div class="main">
    <h1>Register Physician</h1>
    <div class="registerDiv">
        <form id="registerForm" action="includes/register.php" method="POST">
            <table>
                <tr>
                    <td class="title">First Name</td>
                    <td><input class="holo" id="fnameInput" name="fname" type="text" /></td>
                    <td class="star">*</td>
                </tr>
                <tr>
                    <td class="title">Middle Name</td>
                    <td><input class="holo" id="mnameInput" name="mname" type="text" /></td>
                </tr>
                <tr>
                    <td class="title">Last Name</td>
                    <td><input class="holo" id="lnameInput" name="lname" type="text" /></td>
                    <td class="star">*</td>
                </tr>
                <tr>
                    <td class="title">Email</td>
                    <td><input class="holo" id="emailInput" name="email" type="email" /></td>
                    <td class="star">*</td>
                </tr>
                <tr>
                    <td class="title">Date of Birth</td>
                    <td><input class="holo" id="dobInput" name="dob" readonly="true" type="text" /></td>
                </tr>
                <tr>
                    <td class="title">Gender</td>
                    <td style="vertical-align: bottom;">
                        <div id="genderInput">
                            <input id="radioM" class="radio" type="radio" name="gender" value="male"><span class="radioText">Male</span>
                            <input id="radioF" class="radio" type="radio" name="gender" value="female"><span class="radioText">Female</span>
                        </div>
                    </td>
                    <td class="star">*</td>
                </tr>
                <tr>
                    <td class="title">Phone</td>
                    <td><input class="holo" id="phoneInput" name="phone" type="text" /></td>
                </tr>
                <tr>
                    <td class="title">Practice Address</td>
                    <td><input class="holo" id="addrInput" name="address" type="text" /></td>
                </tr>
                <tr>
                    <td class="title">Practice City</td>
                    <td><input class="holo" id="cityInput" name="city" type="text" /></td>
                </tr>
                <tr>
                    <td class="title">Practice State</td>
                    <td>
                        <select class="holo" id="selState" name="state">
                            <option selected="selected" value="">Select State:</option>
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
                    <td class="title">Practice Zip</td>
                    <td><input class="holo" id="zipInput" maxlength="5" name="zip" type="text" /></td>
                </tr>
                <tr>
                    <td class="title">Associated Hospital</td>
                    <td>
                        <select class="holo" id="selHospital" name="hospital">
                            <?php
                            $hospitals = getHospitalsList($mysqli);
                            $hospitals[] = new HospitalItem(1, "Other");
                            array_unshift($hospitals, new HospitalItem(-1, "Select Hospital:"));
                            for ($i = 0; $i < count($hospitals); $i++) {
                                $id = $hospitals[$i]->id;
                                $name = $hospitals[$i]->name;
                                echo("<option value=\"$id\">$name</option>");
                            }
                            ?>
                        </select>
                    </td>
                    <td class="star">*</td>
                </tr>
                <tr id="trHospOther" style="display: none;">
                    <td class="title">Hospital Name</td>
                    <td><input class="holo" id="hospitalInput" name="hospitalOther" type="text" /></td>
                    <td class="star">*</td>
                </tr>
                <tr>
                    <td class="title">NPI Number<img class="question" src="img/question.png" title="You must enter your 10-digit National Provider Identifier Number. This will be used to verify that you are a licensed physician."/></td>
                    <td><input class="holo" id="npiInput" maxLength="10" name="npi" type="text" /></td>
                    <td class="star">*</td>
                </tr>
                <tr id="pwdRow">
                    <td class="title" id="titlePwd">Password<img class="question" src="img/question.png" title="Your password must be at least 8 characters long. We recommend you use more than 8 characters with a combination of numbers, letters (uppercase and lowercase), and punctuation."/></td>
                    <td><input class="holo" id="pwdInput" name="pwd" type="password" /></td>
                    <div id="pwdInfo">
                        <h4>Password must meet the following requirements:</h4>
                        <ul>
                            <li id="letter" class="invalid">At least <strong>one letter</strong></li>
                            <li id="capital" class="invalid">At least <strong>one capital letter</strong></li>
                            <li id="number" class="invalid">At least <strong>one number</strong></li>
                            <li id="special" class="invalid">At least <strong>one special character</strong></li>
                            <li id="length" class="invalid">Be at least <strong>8 characters</strong></li>
                        </ul>
                    </div>
                    <td class="star">*</td>
                </tr>
                <tr id="pwdConfirm">
                    <td class="title">Confirm Password</td>
                    <td>
                        <input class="holo" id="pwdConfirmInput" name="pwdConfirm" type="password" /><img id="pwdConfirmMatch" src="img/cross.png"/>
                    </td>
                    <td class="star">*</td>
                </tr>
            </table>
            <div class="infoReq">
                Fields denoted with an * are required.
            </div>
            <div class="bottomDiv">
                <!--<span class="verify">Verify that you aren't a robot</span>
                <div id="recaptchaWidget" class="g-recaptcha" data-callback="gCaptchaCallback" data-sitekey="6Lc_1wcTAAAAAETzz9dyYqEU0OguS-_NNjQqUQDY"></div>-->
                <div class="button-dark" id="btnRegister">Register</div>
            </div>
        </form>
    </div>
</div>
<div id="footer">
    <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
</div>
</body>
</html>
