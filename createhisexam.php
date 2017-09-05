<?php

/*
Our "config.inc.php" file connects to database
*/
require("config.inc.php");

//if posted data is not empty
if (!empty($_POST)) 
{
    //Check for an exam on this day for this patient. 
    $query = " SELECT ID_column FROM exams  WHERE exam_date = :date AND patient_key =:patientid ";
    $query_params = array
    (
        ':date' => $_POST['date'],
        ':patientid' =>(int) $_POST['patientid']
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
		//If we have made it this far without dying, we have successfully updated the patient data.
		$response["success"] = 1;
		$response["message"] = "Exam for today found!";
		$response["exam_ID"]= $row['ID_column'];
		die( json_encode($response));
		
    }

    //If we have made it here without dying, then we are in the clear to
    //create a new patient. 
    $query = "INSERT INTO exams ( exam_date, patient_key, exam_time, doctor_key ) VALUES ( :date, :patientid, :time, :doctorid ) ";

    //Again, we need to update our tokens with the actual data:
  	$query_params = array
		(
			':date' => $_POST['date'],
			':patientid' => (int) $_POST['patientid'],
			':time' => $_POST['time'],
			':doctorid' => (int) $_POST['doctorid']

		);
	 //time to run our query, and create the user
    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
        $id = $db->lastInsertId('ID_column');

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
    $response["exam_ID"]= $id;
	die(json_encode($response));
} 
else 
{
?>
        <h1>Enter Patient ID and Exam Date</h1>
        <form action="createhisexam.php" method="post">
            Date:<br />
            <input type="text" name="date" value="" />
            <br /><br />

            Patient ID:<br />
            <input type="text" name="patientid" value="" />
            <br /><br />
			
			Time:<br />
            <input type="text" name="time" value="" />
            <br /><br />
			
			Doctorid:<br />
            <input type="text" name="doctorid" value="" />
            <br /><br />

            <input type="submit" value="Submit" />
        </form>
        <?php
}

?>
