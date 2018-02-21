
DROP PROCEDURE IF EXISTS sp_select_patient_info;
DELIMITER ;;
CREATE  PROCEDURE sp_select_patient_info(IN _patientid INT(10) UNSIGNED) 
BEGIN 
    SELECT pat.patient_id AS patientId, pat.username, pat.first_name AS firstName, pat.last_name AS lastName, pat.gender, pat.dob, war.entered_at AS waitingroom, pat.register_at, IF(ex.exam_id IS NOT NULL ,'1','') AS uploaded
    FROM patients pat
    LEFT JOIN waiting_room war ON war.patient_id = pat.patient_id 
    LEFT JOIN exams ex ON ex.patient_id = pat.patient_id 
    WHERE pat.patient_id = _patientid;
END ;;
DELIMITER ;
