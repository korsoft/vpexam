DROP PROCEDURE IF EXISTS sp_register_patient;
DELIMITER ;;
CREATE  PROCEDURE sp_register_patient(
	_firstname VARCHAR(4096), 
	_lastname VARCHAR(4096), 
	_dob VARCHAR(4096),
	_email VARCHAR(2048), 
	_password VARCHAR(4096), 
	_salt VARCHAR(4096), 
	_physicianid INT(10) UNSIGNED)

BEGIN 
	DECLARE _id INT(10) UNSIGNED;
	INSERT INTO patients(username, email, password, salt, first_name, last_name, dob, old_patient_id ) VALUES(_firstname, _email, _password, _salt, _firstname, _lastname, _dob, 0);
	SET _id = LAST_INSERT_ID();
    INSERT INTO patient_physicians( id, physician_id ) VALUES(_id, _physicianid);
    SELECT _id AS patient_id;
END ;;
DELIMITER ;