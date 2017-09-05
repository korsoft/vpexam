<?php
include_once 'includes/register.inc.php';
include_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Secure Login: Registration Form</title>
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script>
        <link rel="stylesheet" href="styles/main.css" />
    </head>
    <body>
        <!-- Registration form to be output if the POST variables are not
        set or if the registration script caused an error. -->
        <h1>Register with us</h1>
        <?php
        if (!empty($error_msg)) {
            echo $error_msg;
        }
        ?>
        <ul>
            <li>Usernames may contain only digits, upper and lower case letters and underscores</li>
            <li>Emails must have a valid email format</li>
            <li>Passwords must be at least 6 characters long</li>
            <li>Passwords must contain
                <ul>
                    <li>At least one upper case letter (A..Z)</li>
                    <li>At least one lower case letter (a..z)</li>
                    <li>At least one number (0..9)</li>
                </ul>
            </li>
            <li>Your password and confirmation must match exactly</li>
        </ul>
        <form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
                method="post" 
                name="registration_form">
	    First Name: <input type='text'
		name='fname'
		id='fname' /><br>
	    Last Name: <input type='text'
		name='lname'
		id='lname' /><br>
            Username: <input type='text' 
                name='username' 
                id='username' /><br>
            Email: <input type="text" name="email" id="email" /><br>
            Password: <input type="password"
                             name="password" 
                             id="password"/><br>
            Confirm password: <input type="password" 
                                     name="confirmpwd" 
                                     id="confirmpwd" /><br>
	    Select Role: 
	    <input type="radio" name="role" value="physician" onclick="disablePhysId()">Physician
<input type="radio" name="role" value="patient" onclick="enablePhysId()">Patient <br>
	    Physician ID: <input type='text'
		name='phys_id'
		id='phys_id'
		disabled /><br>
            <input type="button" 
                   value="Register" 
                   onclick="return regformhash(this.form,
                                   this.form.username,
                                   this.form.email,
                                   this.form.password,
                                   this.form.confirmpwd,
				   this.form.role,
				   this.form.phys_id);" /> 
        </form>
	<script>
		function disablePhysId() {
			var physIdBox = document.registration_form.phys_id;
			physIdBox.value = "";
			physIdBox.disabled = true;
		}

		function enablePhysId() {
			var physIdBox = document.registration_form.phys_id;
			physIdBox.disabled = false;
		}
	</script>
        <p>Return to the <a href="index.php">login page</a>.</p>
    </body>
</html>
