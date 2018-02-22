DROP PROCEDURE IF EXISTS sp_search_patients_by_name;
DELIMITER ;;
CREATE PROCEDURE sp_search_patients_by_name(_name VARCHAR(300))
BEGIN 
    SELECT patient_id AS patientId, patients.first_name AS firstName, patients.middle_name AS middleName, patients.last_name AS lastName, patients.email, patients.gender, patients.dob, patients.phone, address, city, state, zip, 
        insurance_company AS insuranceCompany, insurance_address AS insuranceAddress, insurance_phone AS insurancePhone, insurance_ph_name AS insurancePhName, 
        insurance_patient_relationship AS insurancePatientRelationship, insurance_group_num AS insuranceGroupNum, insurance_id_cert_num AS insuranceIdCertNum, 
        insurance_issue_date AS insuranceIssueDate, 
        CONCAT('[', GROUP_CONCAT(CONCAT('{ "physicianId":', physicians.physician_id, ',"username":"', physicians.username, '","firstName":"', physicians.first_name, '","middleName":"', physicians.middle_name, '","lastName":"', physicians.last_name, '"}') SEPARATOR ','), ']') AS assocPhys
    FROM patients 
    JOIN patient_physicians ON patient_physicians.id = patient_id 
    JOIN physicians         ON physicians.physician_id = patient_physicians.physician_id
    WHERE CONCAT(LOWER(patients.first_name), IF('' != patients.middle_name, CONCAT(' ', LOWER(patients.middle_name)), ''), ' ', LOWER(patients.last_name)) LIKE CONCAT('%', _name, '%')
    GROUP BY patient_id;
END ;;
DELIMITER ;
