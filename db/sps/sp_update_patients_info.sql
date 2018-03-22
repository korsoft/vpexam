DROP PROCEDURE IF EXISTS sp_update_patients_info;
DELIMITER ;;
CREATE  PROCEDURE sp_update_patients_info(
	IN _patient_id INT(10) UNSIGNED, 
	IN _first_name VARCHAR(2048),
	IN _middle_name VARCHAR(2048),
	IN _last_name VARCHAR(2048),
	IN _email VARCHAR(2048),
	IN _gender VARCHAR(2048),
	IN _phone VARCHAR(2048),
	IN _dob VARCHAR(2048),
	IN _address VARCHAR(2048),
	IN _city VARCHAR(2048),
	IN _state VARCHAR(2048),
	IN _zip VARCHAR(2048),
	IN _insurance_company VARCHAR(2048),
	IN _insurance_address VARCHAR(2048),
	IN _insurance_phone VARCHAR(2048),
	IN _insurance_ph_name VARCHAR(2048),
	IN _insurance_patient_relationship VARCHAR(2048),
	IN _insurance_group_num VARCHAR(2048),
	IN _insurance_id_cert_num VARCHAR(2048),
	IN _insurance_issue_date VARCHAR(2048),
	IN _password VARCHAR(2048),
	IN _salt VARCHAR(2048)
	) 
BEGIN 
	DECLARE flag TINYINT(1) DEFAULT 0;
	DECLARE toupdate VARCHAR(2048) DEFAULT '';
    IF (_first_name IS NOT NULL AND _first_name != '') THEN
    	SET flag =1;
        SET toupdate = CONCAT('first_name = \'', _first_name,'\'');
    END IF;
    IF (_middle_name IS NOT NULL AND _middle_name != '') THEN
        SET toupdate = CONCAT(toupdate,IF(toupdate != '',',',''),' middle_name = \'', _middle_name,'\'');
    END IF;
    IF (_last_name IS NOT NULL AND _last_name != '') THEN
    	SET flag =1;
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' last_name = \'', _last_name,'\'');
    END IF;
    IF (_email IS NOT NULL AND _email != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''), ' email = \'', _email,'\'');
    END IF;
    IF (_gender IS NOT NULL AND _gender != '') THEN
    	SET flag =1;
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' gender = \'', _gender,'\'');
    END IF;
    IF (_phone IS NOT NULL AND _phone != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' phone = \'', _phone,'\'');
    END IF;
    IF (_dob IS NOT NULL AND _dob != '') THEN
    	SET flag =1;
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' dob = \'', _dob,'\'');
    END IF;
    IF (_address IS NOT NULL AND _address!= '' ) THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' address = \'', _address,'\'');
    END IF;
    IF (_city IS NOT NULL AND _city != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' city = \'', _city,'\'');
    END IF;
    IF (_state IS NOT NULL AND _state != '') THEN 
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' state = \'', _state,'\'');
    END IF;
    IF (_zip IS NOT NULL  AND _zip != '') THEN 
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' zip = \'', _zip,'\'');
    END IF;
    IF (_insurance_company IS NOT NULL AND _insurance_company != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''), ' insurance_company = \'', _insurance_company,'\'');
    END IF;
    IF (_insurance_address IS NOT NULL AND _insurance_address != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_address = \'', _insurance_address,'\'');
    END IF;
    IF (_insurance_phone IS NOT NULL AND _insurance_phone != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_phone = \'', _insurance_phone,'\'');
    END IF;
    IF (_insurance_ph_name IS NOT NULL AND _insurance_ph_name != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_ph_name = \'', _insurance_ph_name,'\'');
    END IF;
    IF (_insurance_patient_relationship IS NOT NULL AND _insurance_patient_relationship != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_patient_relationship = \'', _insurance_patient_relationship,'\'');
    END IF;
    IF (_insurance_group_num IS NOT NULL AND _insurance_group_num != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_group_num = \'', _insurance_group_num,'\'');
    END IF;
    IF (_insurance_id_cert_num IS NOT NULL AND _insurance_id_cert_num != '') THEN 
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_id_cert_num = \'', _insurance_id_cert_num,'\'');
    END IF;
    IF (_insurance_issue_date IS NOT NULL AND _insurance_issue_date != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' insurance_issue_date = \'', _insurance_issue_date,'\'');
    END IF;
    IF (_password IS NOT NULL AND _password != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' password = \'', _password,'\'');
    END IF;
    IF (_salt IS NOT NULL AND _salt != '') THEN
        SET toupdate = CONCAT(toupdate, IF(toupdate != '',',',''),' salt = \'', _salt,'\'');
    END IF;
    IF '' != toupdate THEN
          SET @query = CONCAT('UPDATE patients SET ', toupdate,' WHERE patient_id = ',_patient_id);
          PREPARE stmt FROM @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        IF flag = 1 THEN
	    	UPDATE patients SET username = LOWER(CONCAT(first_name, last_name, DATE_FORMAT(dob,'%m%d%Y'), SUBSTRING(gender,1,1))) WHERE patient_id = _patient_id;
	    END IF;
    END IF;
END ;;
DELIMITER ;