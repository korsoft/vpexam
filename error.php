<?php
    include_once 'includes/functions.php';
    include_once 'includes/error_codes.php';
    $error = $_GET['error'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="msapplication-TileColor" content="#2d89ef">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <title>Error</title>
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
        <link rel="stylesheet" type="text/css" href="<?php autoVerCss('/style/error.css');?>">
        <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <link rel="manifest" href="/manifest.json">
    </head>
    <body>
        <div id="header">
            <div id="logo">
                <div id="logo_text">
                    <img src="img/logo_img.png" />
                </div>
                <div class="topmenu">
                    <div class="topmenuItem hoverable" id="btnTopHome">
                        <a href="main.php"><span>Main</span></a>
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
                        <span>Contact Us</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="main">
            <h1>Uh oh, it looks like there was an error...</h1>
            <h2><?php echo(ERROR_CODES[$error]); ?></h2>
            <h2><?php echo('Line Number ') . $_GET['l'] ?></h2>
            <h3>If this error persists, please contact the <a href="mailto:esv5@zips.uakron.edu?subject=Error%20<?php echo($error); ?>">site administrator</a>.</h3>
        </div>
        <div id="footer">
            <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
        </div>
    </body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-latest.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php autoVerCss('/js/sha512.js');?>"></script>
    <script type="text/javascript" src="<?php autoVerCss('/js/forms.js');?>"></script>
    <script type="text/javascript" src="<?php autoVerCss('/js/error.js');?>"></script>
</html>
