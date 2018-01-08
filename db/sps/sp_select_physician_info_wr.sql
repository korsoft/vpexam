DROP PROCEDURE IF EXISTS sp_select_physician_info_wr;
DELIMITER ;;
CREATE  PROCEDURE sp_select_physician_info_wr(IN _patientid INT(10) UNSIGNED) 
BEGIN 
    SELECT patient_physicians.physician_id as id, IFNULL(CONCAT(physicians.first_name , ' ', physicians.last_name), '') AS physicians_name,physicians.username AS waiting_room
    FROM patients 
    INNER JOIN patient_physicians ON id = patient_id 
    INNER JOIN physicians ON physicians.physician_id = patient_physicians.physician_id
    WHERE patient_id = _patientid;
END ;;
DELIMITER ;
