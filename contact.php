<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';

    sec_session_start();

    $userloggedid = isset($_SESSION['user_id'])?$_SESSION['user_id']:0;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html" http-equiv="content-type">
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <title>VPExam - Contact Us</title>
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
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/contact.css');?>">
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/style.css');?>">
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
                        <div class="topmenuItem hoverable" id="btnTopContact">
                            <a href="main.php"><span>Main</span></a>
                        </div>
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
                <div class="contactDiv">
                    <h2>Contact Us</h2>
                    <p>
                        James V. Cireddu MD<br/>
                        Founder/CEO<br/>
                        TeleHealth Care Solutions, LLC<br/>
                        <a href="mailto:cireddujv@gmail.com">cireddujv@gmail.com</a><br/><br/>
                        Coming Soon!<br/>
                        Full Customer Service
                    </p>
                </div>
            </div>
            <div id="content_footer"></div>
            <div id="footer">
                <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
            </div>
        </div>
    </body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php autoVerCss('/js/sha512.js');?>"></script>
    <script type="text/javascript" src="<?php autoVerCss('/js/forms.js');?>"></script>
    <script type="text/javascript" src="<?php autoVerCss('/js/contact.js');?>"></script>
</html>