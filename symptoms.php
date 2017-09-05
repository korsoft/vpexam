<?php

//load and connect to MySQL database stuff
require("config.inc.php");

if (!empty($_POST))
{
     //initial query
   $query = "UPDATE exams SET 
    weight_loss= :1 , night_sweats= :2, fatigue = :3,rash =:4, recent_falls = :5, 
	passing_out = :6,visual_changes= :7, headache =:8, eye_pain =:9, double_vision =:10,
	blind_spots= :11, floaters =:12,runny_nose = :13, nose_bleeds = :14, ear_pain =:15,
	tooth_ashe =:16, sore_throat =:17, pain_swallowing =:18, chest_pain =:19, unable_to_exercise =:20,
	wake_gasping =:21, short_breath_flat=:22,  leg_swell =:23,rapid_heart =:24, cough =:25,
	short_of_breath=:26, wheezing =:27, sputum =:28, blood_in_sputum =:29,ab_pain =:30, 
	weight_loss2 =:31, diff_swallow =:32, indegestion =:33, bloating =:34, loss_of_app =:35,
	nausea_vomit =:36, diarrhea =:37, constipation =:38, vomit_blood = :39, blood_dark_stool =:40,
	incontinence =:41, pain_urine =:42, freq_urine =:43, diff_urine = :44, weak_stream =:45, 
	arm_or_leg_pain =:46,morning_stiff = :47, end_of_day_stiff =:48, joint_swell =:49, difficult_walk =:50,
	change_site =:51,change_smell =:52, change_hear =:53, change_taste =:54, seizure =:55,
	headache2 =:56, pins_needles =:57,numbness =:58, limb_weak =:59, poor_balance =:60,
	speech_prob =:61, depression =:62, diff_sleep =:63, anxiety =:64, diff_concen =:65,
	lack_energy =:66, excessive_energy = :67, easy_bruise =:68, dental_blood =:69, fever=:70 
    WHERE ID_column  = :id ";

    //Update query
    $query_params = array(
        ':id' => (int) $_POST['id'],
        ':1' =>(boolean) $_POST['1'],
        ':2' => (boolean)$_POST['2'],
        ':3' => (boolean)$_POST['3'],
        ':4' => (boolean)$_POST['4'],
        ':5' => (boolean)$_POST['5'],
        ':6' =>(boolean) $_POST['6'],
        ':7' => (boolean)$_POST['7'],
		':8' => (boolean)$_POST['8'],
        ':9' => (boolean)$_POST['9'],
		':10' =>(boolean) $_POST['10'],
		':11' => (boolean)$_POST['11'],
        ':12' => (boolean)$_POST['12'],
        ':13' => (boolean)$_POST['13'],
        ':14' => (boolean)$_POST['14'],
        ':15' => (boolean)$_POST['15'],
        ':16' => (boolean)$_POST['16'],
        ':17' => (boolean)$_POST['17'],
		':18' => (boolean)$_POST['18'],
        ':19' => (boolean)$_POST['19'],
		':20' => (boolean)$_POST['20'],
		':21' => (boolean)$_POST['21'],
        ':22' => (boolean)$_POST['22'],
        ':23' => (boolean)$_POST['23'],
        ':24' => (boolean)$_POST['24'],
        ':25' => (boolean)$_POST['25'],
        ':26' => (boolean)$_POST['26'],
        ':27' => (boolean)$_POST['27'],
		':28' => (boolean)$_POST['28'],
        ':29' => (boolean)$_POST['29'],
		':30' => (boolean)$_POST['30'],
		':31' => (boolean)$_POST['31'],
        ':32' => (boolean)$_POST['32'],
        ':33' => (boolean)$_POST['33'],
        ':34' => (boolean)$_POST['34'],
        ':35' => (boolean)$_POST['35'],
        ':36' => (boolean)$_POST['36'],
        ':37' => (boolean)$_POST['37'],
		':38' => (boolean)$_POST['38'],
        ':39' => (boolean)$_POST['39'],
		':40' => (boolean)$_POST['40'],
		':41' => (boolean)$_POST['41'],
        ':42' => (boolean)$_POST['42'],
        ':43' => (boolean)$_POST['43'],
        ':44' => (boolean)$_POST['44'],
        ':45' => (boolean)$_POST['45'],
        ':46' => (boolean)$_POST['46'],
        ':47' => (boolean)$_POST['47'],
		':48' => (boolean)$_POST['48'],
        ':49' => (boolean)$_POST['49'],
		':50' => (boolean)$_POST['50'],
		':51' => (boolean)$_POST['51'],
        ':52' => (boolean)$_POST['52'],
        ':53' => (boolean)$_POST['53'],
        ':54' => (boolean)$_POST['54'],
        ':55' => (boolean)$_POST['55'],
        ':56' => (boolean)$_POST['56'],
        ':57' => (boolean)$_POST['57'],
		':58' => (boolean)$_POST['58'],
        ':59' => (boolean)$_POST['59'],
		':60' => (boolean)$_POST['60'],
		':61' => (boolean)$_POST['61'],
        ':62' => (boolean)$_POST['62'],
        ':63' => (boolean)$_POST['63'],
        ':64' => (boolean)$_POST['64'],
        ':65' => (boolean)$_POST['65'],
        ':66' => (boolean)$_POST['66'],
        ':67' => (boolean)$_POST['67'],
		':68' => (boolean)$_POST['68'],
        ':69' => (boolean)$_POST['69'],
		':70' => (boolean)$_POST['70']
		
    );

        //execute query
    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);

    }
    catch (PDOException $ex) {

        $response["success"] = 0;
        $response["message"] = "Failed to run query: " . $ex->getMessage();
        die(json_encode($response));
    }

    $response["success"] = 1;
    $response["message"] = "Symptoms Successfully Added!";

    echo json_encode($response);

} else
{
?>

                <h1>Add Symptoms</h1>
                <form action="symptoms.php" method="post">
				
                    Exam id:<br />
                    <input type="text" name="id" placeholder="id" />
                    <br /><br />

                   <input type="submit" value="Add Symptoms" />
				   
                </form>
 <?php
}

?>
