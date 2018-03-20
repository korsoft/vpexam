DROP PROCEDURE IF EXISTS sp_register_patient_physician;
DELIMITER ;;
CREATE  PROCEDURE sp_register_patient_physician(
	_patientid INT(10) UNSIGNED, 
	_physicianid INT(10) UNSIGNED)
BEGIN 
	DECLARE _id INT(10) UNSIGNED;
        IF _physicianid>0 THEN
            INSERT INTO patient_physicians( id, physician_id ) VALUES(_patientid, _physicianid);
            SET _id = _patientid;
        END IF;
    SELECT _id AS patient_id;
END ;;
DELIMITER ;