DROP PROCEDURE IF EXISTS sp_select_email_physician_patient;
DELIMITER ;;
CREATE  PROCEDURE sp_select_email_physician_patient(IN _email VARCHAR(150)) 
BEGIN 
    DECLARE intTotal TINYINT(1) DEFAULT 0;
    DECLARE patientId INT(8) DEFAULT 0;
    DECLARE firstName VARCHAR(255) DEFAULT '';
    process1: BEGIN 
        SELECT COUNT(*),patient_id, first_name INTO intTotal, patientId, firstName FROM patients WHERE email = _email LIMIT 1;
    END;
    process2: BEGIN 
        IF (intTotal=0) THEN
            SELECT COUNT(*) INTO intTotal FROM physicians WHERE email = _email LIMIT 1;
        END IF;
    END;
    SELECT intTotal, patientId, firstName;
END ;;
DELIMITER ;
