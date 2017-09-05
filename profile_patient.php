<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Patient Profile</title>
	<style>
		#header {
			background-color:black;
			color:white;
			text-align:center;
			padding:5px;
		}

		#section {
			width:relative;
			float:left;
			padding:10px;
		}
	</style>
	<script>
		function _(el) {
			return document.getElementById(el);
		}
		function showInputs() {
			_("email").style.visibility = "visible";
			_("mrn").style.visibility = "visible";
			_("gender").style.visibility = "visible";
			_("phone").style.visibility = "visible";
			_("dob").style.visibility = "visible";
			_("address").style.visibility = "visible";
			_("city").style.visibility = "visible";
			_("state").style.visibility = "visible";
			_("zip").style.visibility = "visible";
			_("btn_hide").style.visibility = "visible";
			_("btn_update").style.visibility = "visible";
			_("dob_info").style.visibility = "visible";
		}
		function hideInputs() {
			_("email").style.visibility = "hidden";
			_("mrn").style.visibility = "hidden";
			_("gender").style.visibility = "hidden";
			_("phone").style.visibility = "hidden";
			_("dob").style.visibility = "hidden";
			_("address").style.visibility = "hidden";
			_("city").style.visibility = "hidden";
			_("state").style.visibility = "hidden";
			_("zip").style.visibility = "hidden";
			_("btn_hide").style.visibility = "hidden";
			_("btn_update").style.visibility = "hidden";
			_("dob_info").style.visibility = "hidden";
		}
	</script>
</head>

<body>
<?php if ((login_check($mysqli) == true) && ($_SESSION['is_patient'] == true)) : ?>
	<?php $_SESSION['patient_info'] = getExtendedPatientInfo($_SESSION['user_id'], $mysqli); ?>
	<div id="header">
		<h1> My Profile</h1>
	</div>
	
	<form action="profile_update.php" method="POST">
		<div id="section">
			<p><b>First Name</b>: <?php echo $_SESSION['patient_info']->firstName; ?><p>
			<p><b>Last Name</b>: <?php echo $_SESSION['patient_info']->lastName; ?><p>
			<p><b>Username</b>: <?php echo $_SESSION['patient_info']->username; ?><p>
			<p><b>Email</b>: <?php echo $_SESSION['patient_info']->email; ?><input type="email" id="email" name="email" value="<?php echo($_SESSION['patient_info']->email); ?>" style="margin-left:50px;visibility:hidden"></input><p>
			<p><b>MRN</b>: <?php echo $_SESSION['patient_info']->mrn; ?><input type="text" id="mrn" name="mrn" value="<?php echo($_SESSION['patient_info']->mrn); ?>" style="margin-left:50px;visibility:hidden"></input><p>
			<p><b>Gender</b>: <?php echo $_SESSION['patient_info']->gender; ?><select id="gender" name="gender" style="margin-left:50px;visibility:hidden">
				<option value="male" <?php if ($_SESSION['patient_info']->gender == 'male') { echo("selected=\"selected\""); } ?>>M</option>
				<option value="female" <?php if ($_SESSION['patient_info']->gender == 'female') { echo("selected=\"selected\""); } ?>>F</option>
			</select><p>
			<p><b>Phone</b>: <?php echo $_SESSION['patient_info']->phone; ?><input type="number" id="phone" name="phone" value="<?php echo($_SESSION['patient_info']->phone); ?>" style="margin-left:50px;visibility:hidden"></input><p>
			<p><b>Birthdate</b>: <?php echo $_SESSION['patient_info']->dob; ?><input type="text" id="dob" name="dob" value="<?php echo($_SESSION['patient_info']->dob); ?>" style="margin-left:50px;visibility:hidden"><label id="dob_info" style="margin-left:10px;visibility:hidden;">(Must be in form: YYYY-MM-DD)</label></input><p>
			<p><b>Address</b>: <?php echo $_SESSION['patient_info']->address; ?><input type="text" id="address" name="address" value="<?php echo($_SESSION['patient_info']->address); ?>" style="margin-left:50px;visibility:hidden"></input><p>
			<p><b>City</b>: <?php echo $_SESSION['patient_info']->city; ?><input type="text" id="city" name="city" value="<?php echo($_SESSION['patient_info']->city); ?>" style="margin-left:50px;visibility:hidden"><p>
			<p><b>State</b>: <?php echo $_SESSION['patient_info']->state; ?><select id="state" name="state" style="margin-left:50px;visibility:hidden">
				<option value="AL" <?php if ($_SESSION['patient_info']->state == 'AL') { echo("selected=\"selected\""); } ?>>Alabama</option>
<option value="AK" <?php if ($_SESSION['patient_info']->state == 'AK') { echo("selected=\"selected\""); } ?>>Alaska</option><option value="AZ" <?php if ($_SESSION['patient_info']->state == 'AZ') { echo("selected=\"selected\""); } ?>>Arizona</option><option value="AR" <?php if ($_SESSION['patient_info']->state == 'AR') { echo("selected=\"selected\""); } ?>>Arkansas</option><option value="CA" <?php if ($_SESSION['patient_info']->state == 'CA') { echo("selected=\"selected\""); } ?>>California</option><option value="CO" <?php if ($_SESSION['patient_info']->state == 'CO') { echo("selected=\"selected\""); } ?>>Colorado</option><option value="CT" <?php if ($_SESSION['patient_info']->state == 'CT') { echo("selected=\"selected\""); } ?>>Connecticut</option><option value="DE" <?php if ($_SESSION['patient_info']->state == 'DE') { echo("selected=\"selected\""); } ?>>Delaware</option><option value="DC" <?php if ($_SESSION['patient_info']->state == 'DC') { echo("selected=\"selected\""); } ?>>District of Columbia</option><option value="FL" <?php if ($_SESSION['patient_info']->state == 'FL') { echo("selected=\"selected\""); } ?>>Florida</option><option value="GA" <?php if ($_SESSION['patient_info']->state == 'GA') { echo("selected=\"selected\""); } ?>>Georgia</option>
<option value="HI" <?php if ($_SESSION['patient_info']->state == 'HI') { echo("selected=\"selected\""); } ?>>Hawaii</option><option value="ID" <?php if ($_SESSION['patient_info']->state == 'ID') { echo("selected=\"selected\""); } ?>>Idaho</option><option value="IL" <?php if ($_SESSION['patient_info']->state == 'IL') { echo("selected=\"selected\""); } ?>>Illinois</option><option value="IN" <?php if ($_SESSION['patient_info']->state == 'IN') { echo("selected=\"selected\""); } ?>>Indiana</option><option value="IA" <?php if ($_SESSION['patient_info']->state == 'IA') { echo("selected=\"selected\""); } ?>>Iowa</option><option value="KS" <?php if ($_SESSION['patient_info']->state == 'KS') { echo("selected=\"selected\""); } ?>>Kansas</option><option value="KY" <?php if ($_SESSION['patient_info']->state == 'KY') { echo("selected=\"selected\""); } ?>>Kentucky</option><option value="LA" <?php if ($_SESSION['patient_info']->state == 'LA') { echo("selected=\"selected\""); } ?>>Louisiana</option><option value="ME" <?php if ($_SESSION['patient_info']->state == 'ME') { echo("selected=\"selected\""); } ?>>Maine</option><option value="MD" <?php if ($_SESSION['patient_info']->state == 'MD') { echo("selected=\"selected\""); } ?>>Maryland</option>
<option value="MA" <?php if ($_SESSION['patient_info']->state == 'MA') { echo("selected=\"selected\""); } ?>>Massachusetts</option><option value="MI" <?php if ($_SESSION['patient_info']->state == 'MI') { echo("selected=\"selected\""); } ?>>Michigan</option><option value="MN" <?php if ($_SESSION['patient_info']->state == 'MN') { echo("selected=\"selected\""); } ?>>Minnesota</option><option value="MS" <?php if ($_SESSION['patient_info']->state == 'MS') { echo("selected=\"selected\""); } ?>>Mississippi</option><option value="MO" <?php if ($_SESSION['patient_info']->state == 'MO') { echo("selected=\"selected\""); } ?>>Missouri</option><option value="MT" <?php if ($_SESSION['patient_info']->state == 'MT') { echo("selected=\"selected\""); } ?>>Montana</option><option value="NE" <?php if ($_SESSION['patient_info']->state == 'NE') { echo("selected=\"selected\""); } ?>>Nebraska</option><option value="NV" <?php if ($_SESSION['patient_info']->state == 'NV') { echo("selected=\"selected\""); } ?>>Nevada</option><option value="NH" <?php if ($_SESSION['patient_info']->state == 'NH') { echo("selected=\"selected\""); } ?>>New Hampshire</option><option value="NJ" <?php if ($_SESSION['patient_info']->state == 'NJ') { echo("selected=\"selected\""); } ?>>New Jersey</option>
<option value="NM" <?php if ($_SESSION['patient_info']->state == 'NM') { echo("selected=\"selected\""); } ?>>New Mexico</option><option value="NY" <?php if ($_SESSION['patient_info']->state == 'NY') { echo("selected=\"selected\""); } ?>>New York</option><option value="NC" <?php if ($_SESSION['patient_info']->state == 'NC') { echo("selected=\"selected\""); } ?>>North Carolina</option><option value="ND" <?php if ($_SESSION['patient_info']->state == 'ND') { echo("selected=\"selected\""); } ?>>North Dakota</option><option value="OH" <?php if ($_SESSION['patient_info']->state == 'OH') { echo("selected=\"selected\""); } ?>>Ohio</option><option value="OK" <?php if ($_SESSION['patient_info']->state == 'OK') { echo("selected=\"selected\""); } ?>>Oklahoma</option><option value="OR" <?php if ($_SESSION['patient_info']->state == 'OR') { echo("selected=\"selected\""); } ?>>Oregon</option><option value="PA" <?php if ($_SESSION['patient_info']->state == 'PA') { echo("selected=\"selected\""); } ?>>Pennsylvania</option><option value="RI" <?php if ($_SESSION['patient_info']->state == 'RI') { echo("selected=\"selected\""); } ?>>Rhode Island</option><option value="SC" <?php if ($_SESSION['patient_info']->state == 'SC') { echo("selected=\"selected\""); } ?>>South Carolina</option>
<option value="SD" <?php if ($_SESSION['patient_info']->state == 'SD') { echo("selected=\"selected\""); } ?>>South Dakota</option><option value="TN" <?php if ($_SESSION['patient_info']->state == 'TN') { echo("selected=\"selected\""); } ?>>Tennessee</option><option value="TX" <?php if ($_SESSION['patient_info']->state == 'TX') { echo("selected=\"selected\""); } ?>>Texas</option><option value="UT" <?php if ($_SESSION['patient_info']->state == 'UT') { echo("selected=\"selected\""); } ?>>Utah</option><option value="VT" <?php if ($_SESSION['patient_info']->state == 'VT') { echo("selected=\"selected\""); } ?>>Vermont</option><option value="VA" <?php if ($_SESSION['patient_info']->state == 'VA') { echo("selected=\"selected\""); } ?>>Virginia</option><option value="WA" <?php if ($_SESSION['patient_info']->state == 'WA') { echo("selected=\"selected\""); } ?>>Washington</option><option value="WV" <?php if ($_SESSION['patient_info']->state == 'WV') { echo("selected=\"selected\""); } ?>>West Virginia</option><option value="WI" <?php if ($_SESSION['patient_info']->state == 'WI') { echo("selected=\"selected\""); } ?>>Wisconsin</option><option value="WY" <?php if ($_SESSION['patient_info']->state == 'WY') { echo("selected=\"selected\""); } ?>>Wyoming</option>
<option value="AS" <?php if ($_SESSION['patient_info']->state == 'AS') { echo("selected=\"selected\""); } ?>>American Samoa</option><option value="GU" <?php if ($_SESSION['patient_info']->state == 'GU') { echo("selected=\"selected\""); } ?>>Guam</option><option value="MP" <?php if ($_SESSION['patient_info']->state == 'MP') { echo("selected=\"selected\""); } ?>>Northern Mariana Islands</option><option value="PR" <?php if ($_SESSION['patient_info']->state == 'PR') { echo("selected=\"selected\""); } ?>>Puerto Rico</option><option value="VI" <?php if ($_SESSION['patient_info']->state == 'VI') { echo("selected=\"selected\""); } ?>>Virgin Islands</option><option value="FM" <?php if ($_SESSION['patient_info']->state == 'FM') { echo("selected=\"selected\""); } ?>>Federated States of Micronesia</option><option value="MH" <?php if ($_SESSION['patient_info']->state == 'MH') { echo("selected=\"selected\""); } ?>>Marshall Islands</option><option value="PW" <?php if ($_SESSION['patient_info']->state == 'PW') { echo("selected=\"selected\""); } ?>>Palau</option><option value="AA" <?php if ($_SESSION['patient_info']->state == 'AA') { echo("selected=\"selected\""); } ?>>Armed Forces - Americas</option><option value="AE" <?php if ($_SESSION['patient_info']->state == 'AE') { echo("selected=\"selected\""); } ?>>Armed Forces - Europe</option>
<option value="AP" <?php if ($_SESSION['patient_info']->state == 'AP') { echo("selected=\"selected\""); } ?>>Armed Forces - Pacific</option>
			</select><p>
			<p><b>Zip</b>: <?php echo $_SESSION['patient_info']->zip; ?><input type="number" id="zip" name="zip" value="<?php echo($_SESSION['patient_info']->zip); ?>" style="margin-left:50px;visibility:hidden"></input><p>
			<p><button id="btn_edit" type="button" onclick="showInputs()">Edit Info</button><br>
			<br><button id="btn_hide" type="button" onclick="hideInputs()" style="visibility:hidden">Hide Edit Fields</button><br>
			<br><button id="btn_update" type="submit" id="submit" style="visibility:hidden">Update Info</button><br>
		</div>
	</form>

<?php else : ?>
	<p>
		<span class="error">You are not authorized to access this page.</span> Please <a href="login.php">login</a>.
	</p>
<?php endif; ?>
</body>
</html>
