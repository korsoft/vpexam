<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();

    $userloggedid = isset($_SESSION['user_id'])?$_SESSION['user_id']:0;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=windows-1252" http-equiv="content-type">
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <meta name="description" content="website description">
        <meta name="keywords" content="website keywords, website keywords">
        <title>VPExam - Main</title>
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
        <link rel="stylesheet" type="text/css" href="/style/style.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.0.7/css/swiper.min.css">
        <link rel="manifest" href="/manifest.json">
    </head>
    <body>
        <div id="main">
            <div id="header">
                <div id="logo">
                    <div id="logo_text">
                        <img src="img/logo_img.png" />
                    </div>
                    <div class="topmenu">
                        <!--<span class="topmenuItem split">|</span>-->
                        <div class="topmenuItem hoverable">
                            <?php if(0 < $userloggedid) { ?>
                                <a href="<?php echo true == $_SESSION['is_patient']?'patient_main':'physician_main'; ?>.php"><span>Home</span></a>
                            <?php } else { ?>
                                <span id="btnTopLogin">Login</span>
                            <?php } ?>
                        </div>
                        <div id="sidebarLoginPhysician">
                            <div class="arrow-up"></div>
                            <div>
                                <div class="sidebar_item">
                                    <h3>Login</h3>
                                    <form id="loginForm" class="loginForm" method="post" action="includes/process_login.php">
                                        <label for="user" class="loginW">Username:</label><br>
                                        <input class="login" name="username" id="user" type="text"><br>
                                        <br>
                                        <label for="user" class="loginW">Password:</label><br>
                                        <input class="login" name="password" id="password" type="password"><br>
                                        <br>
                                        <input name="remember" type="checkbox"><label for="remember" class="loginW">Remember
                                        Me</label><br>
                                        <br>
                                        <div id="btnLogin" name="btnLogin" class="button-dark" value="Login" type="button">
                                            Login
                                        </div>
                                    </form>
                                    <br>
                                </div>
                            </div>
                        </div>
                        <div class="topmenuItem hoverable" id="btnTopContact">
                            <a href="contact.php"><span>Contact Us</span></a>
                        </div>
                    </div>
                    <?php if(isset($_SESSION['first_name'])) { ?>
                        <div class="welcomeDiv">
                            Welcome, <?php echo "{$_SESSION['first_name']} {$_SESSION['last_name']}" ?>!
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div id="content_header"></div>
                <div id="site_content">
                    <div id="centerContent">
                        <img src="img/banner.png" alt="Logo Banner" >
                    </div>
                    <!-- Login Div goes here -->
                    <div class="aboutDiv">
                        <p id="introText">The Virtual Physical Exam platform provides comprehensive, personalized Telemedicine for clinicians and patients.</p>
                        <br />
                        <div class="signUpContainerDiv">
                            <div class="signUpInnerDiv">
                                <div class="signUpDiv">
                                    <h3>Are you a patient?</h3>
                                    <p>Register now for an account.</p>
                                    <div class="button-dark" <?php echo (0 == $userloggedid?'id="btnRegister"':'') ?>>Register</div>
                                    <br /><br />
                                    <div class="signUpFeatureDiv" id="free">
                                        <span>100% Free</span><img src="img/free.png">
                                    </div>
                                    <div class="signUpFeatureDiv" id="secure">
                                        <span>100% Secure</span><img src="img/lock.png">
                                    </div>
                                </div>
                                <div class="signUpDiv2">
                                    <h3>Are you a physician?</h3>
                                    <p>Request your free 30-day trial today.</p>
                                    <br/>
                                    <div class="button-dark" <?php echo (0 == $userloggedid?'id="btnRequestTrial"':'') ?>>Create New Account</div>
                                </div>
                            </div>
                        </div>
                        <br /><br />
                        <div class="blockDiv">
                            <h1>Virtual Physical Exam Mobile App</h1>
                            <p>Your solution to comprehensive, personalized Telemedicine.</p>
                            <br />
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="img/1.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/2.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/3.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/4.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/5.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/6.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/7.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/8.png">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="img/9.png">
                                    </div>
                                </div>
                                <!-- Add Pagination -->
                                <div class="swiper-pagination"></div>
                                <!-- Add Arrows -->
                                <div class="swiper-button-next swiper-button-white"></div>
                                <div class="swiper-button-prev swiper-button-white"></div>
                            </div>
                            <br /><br />
                            <div id="divAvailSoon">
                                <p>Available soon on Google Play!</p>
                                <img id="googlePlayLink" src="https://play.google.com/intl/en_us/badges/images/generic/en-play-badge.png" alt="Available on Google Play Logo">
                            </div>
                        </div>
                        <div class="descrDiv">
                            <h1>About VPExam</h1>
                            <p>
                                VPExam.com is your Telemedicine Portal for both Patients and Physicians:
                            </p>
                            <ul>
                                <li>Patients can actively participate in their care/view and update your profile/respond to the Virtual History</li>
                                <li>Physicians' home for reviewing patients' Virtual Physical Exams</li>
                                <li>Focus on your patients instead of documentation/auto-populate notes and effectively review comprehensive exams</li>
                                <li>Connect in real-time/Live video conferencing through <a href="https://doxy.me/">Doxy.me</a> - Coming Soon!</li>
                                <!--<li>Portal for patients allowing them to view and update profile information</li>
                                <li>Portal for clinicians allowing them to view patients' exams</li>
                                <li>Features easy controls for moving from one part of the exam to the next</li>
                                <li>"Clipboard" feature provided so that clinicians can easily document any abnormalities and make notes</li>
                                <li>"My Normal" feature allows clinicians to easily fill out the "Clipboard" with predetermined text</li>
                                <li>Waveform view provides graphical representation of auscultation audio</li>
                                <li>Live video conferencing management through <a href="https://doxy.me/">Doxy.me</a> - Coming Soon!</li>-->
                            </ul>
                            <br /><br />
                            <h1>Security</h1>
                            <p>
                                We take security seriously. <span style="font-weight: bold;">We are 100% HIPAA-compliant.</span> Your sensitive medical information is safe with us.
                                All software and connections associated with VPExam.com are encrypted through SSL. All medical data is stored securely on our server. Read our HIPAA compliance documentation
                                <a href="">here</a>.
                            </p>
                        </div>
                    </div>
                </div>
            <div id="content_footer"></div>
            <div id="footer">
                <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
            </div>
        </div>
        <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.0.7/js/swiper.jquery.min.js"></script>
        <script type="text/javascript" src="js/sha512.js?time=<?php echo time(); ?>"></script>
        <script type="text/javascript" src="js/forms.js"></script>
        <script type="text/javascript" src="js/main.js?time=<?php echo time(); ?>"></script>
    </body>
</html>
