DROP PROCEDURE IF EXISTS sp_select_patients_physician_waitingroom;
DELIMITER ;;
CREATE  PROCEDURE sp_select_patients_physician_waitingroom(IN _physicianid INT(10) UNSIGNED) 
BEGIN 
    SELECT pat.patient_id AS patientId, pat.username, pat.first_name AS firstName, pat.last_name AS lastName, pat.gender, pat.dob, 
    war.entered_at AS waitingroom, pat.register_at, IF(ex.exam_id IS NOT NULL ,'1','') AS uploaded
    FROM patient_physicians pah 
    LEFT JOIN patients pat ON pah.id = pat.patient_id 
    LEFT JOIN waiting_room war ON pah.id = war.patient_id 
    LEFT JOIN exams ex ON ex.patient_id = pat.patient_id 
    LEFT JOIN patients_no_display pnd ON pnd.patient_id = pat.patient_id AND pnd.phys_id = pah.physician_id 
    WHERE pah.physician_id = _physicianid AND pnd.patient_id IS NULL 
    GROUP BY pat.patient_id 
    ORDER BY war.entered_at DESC, pat.register_at DESC, pat.patient_id DESC;
END ;;
DELIMITER ;
