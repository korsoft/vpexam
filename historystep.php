<?php

//load and connect to MySQL database stuff
require("config.inc.php");

if (!empty($_POST))
{
     //initial query
    $query = "UPDATE exams SET :col_name  = :data  WHERE ID_column  = :id ";
        //col_name is the name of the exam step in the table

    //Update query
    $query_params = array(
        ':id' => (int) $_POST['id'],
        ':col_name' => $_POST['col_name'],
        ':data' => $_POST['data']

    );

    //execute query
    try 
	{
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);

    }
    catch (PDOException $ex) 
	{

        $response["success"] = 0;
        $response["message"] = "Failed to run query: " . $ex->getMessage();
        die(json_encode($response));
    }

    $response["success"] = 1;
    $response["message"] = " History Exam Information Successfully Added!";

    echo json_encode($response);

} else
{
?>

                <h1>Add Exam Blob data</h1>
                <form action="examstep.php" method="post">
                    Exam id:<br />
                    <input type="text" name="id" placeholder="id" />
                    <br /><br />
                    Column Name:<br />
                    <input type="text" name="col_name" placeholder="col_name" />
                    <br /><br />
                    Text response data data:<br />
                    <input type="text" name="data" placeholder="data" />
                    <br /><br />
                   <input type="submit" value="Add Exam Data" />
                </form>
 <?php
}
?>


