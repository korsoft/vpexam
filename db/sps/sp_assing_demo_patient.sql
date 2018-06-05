DROP PROCEDURE IF EXISTS sp_assing_demo_patient;
DELIMITER ;;
CREATE  PROCEDURE sp_assing_demo_patient(IN _id INT(6) UNSIGNED) 
BEGIN 
	/* 455 Is the current id for Kristen Chriswell */
	INSERT INTO patient_physicians VALUES(455, _id);
END ;;
DELIMITER ;