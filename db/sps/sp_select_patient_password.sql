DROP PROCEDURE IF EXISTS sp_select_patient_password;
DELIMITER ;;
CREATE  PROCEDURE sp_select_patient_password(IN _patient_id INT(10) UNSIGNED) 
BEGIN 
    SELECT password, salt FROM patients WHERE patient_id = _patient_id;
END ;;
DELIMITER ;
