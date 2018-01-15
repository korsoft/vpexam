DROP PROCEDURE IF EXISTS sp_delete_patient_in_waitingroom;
DELIMITER ;;
CREATE  PROCEDURE sp_delete_patient_in_waitingroom(IN _patientid INT(10) UNSIGNED) 
BEGIN 
    DECLARE idresult INT(6) DEFAULT 0;
    BEGIN 
        IF EXISTS (SELECT patient_id  FROM waiting_room WHERE patient_id = _patientid LIMIT 1) THEN 
            INSERT IGNORE INTO waiting_room_history (physician_id, patient_id, patient_name, entered_at, leaved_at)
            SELECT physician_id, patient_id, patient_name, entered_at, UNIX_TIMESTAMP()
                    FROM waiting_room 
                    WHERE  patient_id = _patientid;

            DELETE FROM waiting_room WHERE patient_id = _patientid;
            SET idresult = _patientid;
        ELSE
            SET idresult = 0;
        END IF;
    END;
    SELECT idresult;
END ;;
DELIMITER ;



