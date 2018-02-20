DROP PROCEDURE IF EXISTS sp_select_patients_physician_waitingroom;
DELIMITER ;;
CREATE  PROCEDURE sp_select_patients_physician_waitingroom(IN _physicianid INT(10) UNSIGNED) 
BEGIN 
    SELECT pat.patient_id AS patientId, pat.username, pat.first_name AS firstName, pat.last_name AS lastName, pat.gender, pat.dob, 
    war.entered_at AS waitingroom, war.uploaded, pat.register_at 
    FROM patient_physicians pah 
    LEFT JOIN patients pat ON pah.id = pat.patient_id 
    LEFT JOIN waiting_room war ON pah.id = war.patient_id 
    LEFT JOIN patients_no_display pnd ON pnd.patient_id = pat.patient_id AND pnd.phys_id = pah.physician_id 
    WHERE pah.physician_id = _physicianid AND pnd.patient_id IS NULL 
    ORDER BY war.entered_at DESC, pat.register_at DESC;
END ;;
DELIMITER ;
