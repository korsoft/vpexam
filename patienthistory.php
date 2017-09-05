<?php

/*
Our "config.inc.php" file connects to database
*/
require("config.inc.php");

//if posted data is not empty
if (!empty($_POST)) {
    //If the first name, last name or mrn is empty when the user submits

    if ( empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['mrn']) )
    {
        // Create some data that will be the JSON response
        $response["success"] = 0;
        $response["message"] = "Please Enter a first name, last name, and mrn";
        $response["uid"] = $_POST['user_id'];
        die(json_encode($response));
    }

    //if the page hasn't died, we will check with our database to see if there is
    //already a patient with that first name last name and mrn in the form. 
    $query = " SELECT id FROM patient_information  WHERE first_name = :first AND last_name =:last AND mrn=:mrn ";
 
    $query_params = array
	(
        ':first' => $_POST['firstname'],
        ':last' => $_POST['lastname'],
        ':mrn' => $_POST['mrn']
    );

    //run the query:
    try 
	{
        // These two statements run the query against your database table.
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
    }
    catch (PDOException $ex) 
	{
        //die("Failed to run query: " . $ex->getMessage());
        $response["success"] = 0;
        $response["message"] = "Database Error1. Please Try Again!";
		die(json_encode($response));
    }

    // If any data is returned,we know that the patient exists so we update the data. 
    $row = $stmt->fetch();
    if ($row) 
	{
		$query = "UPDATE patient_information SET  phone_number = :phone, date_of_birth = :dob, address = :add, city =:city, state = :state, zip = :zip WHERE first_name=:first AND last_name=:last AND mrn=:mrn  ";

		 //Again, we need to update our tokens with the actual data:
		 $query_params = array
        (
            ':first' => $_POST['firstname'],
            ':last' => $_POST['lastname'],
            ':mrn' => $_POST['mrn'],
            ':phone' => $_POST['phone'],
            ':dob' => $_POST['dob'],
            ':add' => $_POST['add'],
            ':city' => $_POST['city'],
            ':state' => $_POST['state'],
            ':zip' => $_POST['zip']
        );


		 //time to run our query, and create the user
		try 
		{
			$stmt   = $db->prepare($query);
			$result = $stmt->execute($query_params);
			


		}
		catch (PDOException $ex) {
		
			//die("Failed to run query: " . $ex->getMessage());

			$response["success"] = 0;
			$response["message"] = "Database Error2. Please Try Again!";
			die(json_encode($response));
		}


		$query =  "SELECT id FROM patient_information WHERE time_stamp = (SELECT MAX( time_stamp ) FROM patient_information) LIMIT 1" ;
		try
		{
		        $stmt   = $db->prepare($query);
                        $result = $stmt->execute();

		}
		catch (PDOException $ex) {

                        //die("Failed to run query: " . $ex->getMessage());

                        $response["success"] = 0;
                        $response["message"] = "Database Error 5 . Please Try Again!" . $ex->getMessage();
                        die(json_encode($response));
                }
		$row = $stmt->fetch();
		//If we have made it this far without dying, we have successfully updated the patient data.
		$response["success"] = 1;
		$response["message"] = "Patient Successfully Updated!";
		$response["patient_id"]=$row['id'];
		die( json_encode($response));
		
		
    }

    //If we have made it here without dying, then we are in the clear to
    //create a new patient. 
    $query = "INSERT INTO patient_information ( first_name, last_name, mrn, phone_number, date_of_birth, address, city, state, zip) VALUES ( :first, :last, :mrn,:phone, :dob, :add, :city, :state, :zip ) ";

    //Again, we need to update our tokens with the actual data:
  	$query_params = array
		(
			':first' => $_POST['firstname'],
			':last' => $_POST['lastname'],
			':mrn' => $_POST['mrn'],
			':phone' => $_POST['phone'],
			':dob' => $_POST['dob'],
			':add' => $_POST['add'],
			':city' => $_POST['city'],
			':state' => $_POST['zip']

		);
	 //time to run our query, and create the user
    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
        $id = $db->lastInsertId('id');

    }
    catch (PDOException $ex) {
        // For testing, you could use a die and message.
        //die("Failed to run query: " . $ex->getMessage());

        $response["success"] = 0;
        $response["message"] = "Database Error3. Please Try Again!";
        die(json_encode($response));
    }

    //If we have made it this far without dying, we have successfully added
    //a new user to our database.  We could do a few things here, such as
    //redirect to the login page.  Instead we are going to echo out some
    //json data that will be read by the Android application, which will login
    //the user (or redirect to a different activity, I'm not sure yet..)
    $response["success"] = 1;
    $response["message"] = "Patient Successfully Added!";
    $response["patient_id"]=(int) $id;
    die(json_encode($response));
} 
else 
{
?>
        <h1>Enter Patient</h1>
        <form action="patienthistory.php" method="post">
            First Name:<br />
            <input type="text" name="firstname" value="" />
            <br /><br />

            Last Name:<br />
            <input type="text" name="lastname" value="" />
            <br /><br />

            Medical Record Number:<br />
            <input type="text" name="mrn" value="" />
            <br /><br />
			
			Phone Number:<br />
            <input type="text" name="phone" value="" />
            <br /><br />
			
			Date of Birth:<br />
            <input type="text" name="dob" value="" />
            <br /><br />
			
			Address:<br />
            <input type="text" name="add" value="" />
            <br /><br />
			
			City:<br />
            <input type="text" name="city" value="" />
            <br /><br />
			
			State:<br />
            <input type="text" name="state" value="" />
            <br /><br />
			
			Zip:<br />
            <input type="text" name="zip" value="" />
            <br /><br />

            <input type="submit" value="Submit" />
        </form>
        <?php
}

?>
