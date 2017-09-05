<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Secure Login: Protected Page</title>
        <link rel="stylesheet" href="styles/main.css" />
    </head>
    <body>
	<?php if (login_check($mysqli) == true) : ?>
    	    <b><p>Welcome <?php echo htmlentities($_SESSION['first_name']." ".$_SESSION['last_name']); ?>!</p></b>
	    <p>Your username is: <?php echo htmlentities($_SESSION['username']); ?></p>
	    <p>Your role is: <?php echo ($_SESSION['is_patient'] ? "patient" : "physician") ?> </p>
            <p>
                This is an example protected page.  To access this page, users
                must be logged in.  At some stage, we'll also check the role of
                the user, so pages will be able to determine the type of user
                authorised to access the page.
            </p>
	    <?php if ($_SESSION['is_patient']) : ?>
		<br>
			<a href="upload_form.php">Go To Upload Page</a>
		</br>
	    <?php endif; ?>
            <p>Return to <a href="index.php">login page</a></p>
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="login.php">login</a>.
            </p>
        <?php endif; ?>
    </body>
</html>
