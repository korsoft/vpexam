<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
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
		<title>VPExam - Registration</title>
		<link rel="stylesheet" href="style/register_success.css" />
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
            <h1>Registration Successful</h1>
            <h2>Thanks for using VPExam!</h2>
        </div>
        <div id="footer">
            <p>Copyright 2015 &#169; TeleHealth Care Solutions</p>
        </div>
	</body>
</html>
