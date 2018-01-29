DROP PROCEDURE IF EXISTS sp_select_patients_by_username;
DELIMITER ;;
CREATE  PROCEDURE sp_select_patients_by_username (IN _username VARCHAR(100)) 
BEGIN 
    SELECT patient_id, first_name, middle_name, last_name, gender,dob,email FROM patients WHERE username = _username;
END ;;
DELIMITER ;
