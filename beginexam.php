<?php

//load and connect to MySQL database stuff
require("config.inc.php");

if (!empty($_POST))
{
        //initial query
        $query = "INSERT INTO exams (exam_date , exam_time,incline, posture,  patient_key, doctor_key  ) VALUES ( :date, :time,:incline, :posture, :patient, :doctor ) ";

    //Update query
    $query_params = array(
        ':date' => $_POST['date'],
        ':time' => $_POST['time'],
        ':incline' => $_POST['incline'],
        ':posture' => $_POST['posture'],
        ':patient' => $_POST['patient'],
        ':doctor' => $_POST['doctor']
    );

        //execute query
    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
        $exam_id =$db->lastInsertId('ID_column');
    }
    catch (PDOException $ex) {
        // For testing, you could use a die and message.
        //die("Failed to run query: " . $ex->getMessage());

        //or just use this use this one:
        $response["success"] = 0;
        $response["message"] = "Database Error. Couldn't add post!";
        die(json_encode($response));
    }

    $response["success"] = 1;
    $response["message"] = "Exam Successfully Added!";
    $response["exam_id"] =(int) $exam_id;

    echo json_encode($response);

} else
{
?>
                <h1>Add New Exam</h1>
                <form action="beginexam.php" method="post">
                    Date:<br />
                    <input type="text" name="date" placeholder="date" />
                    <br /><br />
                    Time:<br />
                    <input type="text" name="time" placeholder="time" />
                    <br /><br />
                    Patient:<br />
                    <input type="text" name="patient" placeholder="patient" />
                    <br /><br />
					Doctor:<br />
                    <input type="text" name="doctor" placeholder="doctor" />
                    <br /><br />
                    <input type="submit" value="Add Exam" />
                </form>
        <?php
}

?>


