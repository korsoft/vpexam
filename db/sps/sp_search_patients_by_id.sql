DROP PROCEDURE IF EXISTS sp_search_patients_by_id;
DELIMITER ;;
CREATE PROCEDURE sp_search_patients_by_id(_id VARCHAR(300))
BEGIN 
    SELECT patient_id AS patientId, patients.first_name AS firstName, patients.middle_name AS middleName, patients.last_name AS lastName, patients.email, patients.gender, patients.dob, patients.phone, address, city, state, zip, 
        insurance_company AS insuranceCompany, insurance_address AS insuranceAddress, insurance_phone AS insurancePhone, insurance_ph_name AS insurancePhName, 
        insurance_patient_relationship AS insurancePatientRelationship, insurance_group_num AS insuranceGroupNum, insurance_id_cert_num AS insuranceIdCertNum, 
        insurance_issue_date AS insuranceIssueDate, 
        IFNULL(CONCAT('[', GROUP_CONCAT(CONCAT('{"physicianId":', physicians.physician_id,', "email":"', physicians.email, '", "username":"', physicians.username, '", "firstName":"', physicians.first_name, '", "middleName":"', physicians.middle_name, '", "lastName":"', physicians.last_name, '", "practiceName":"', physicians.practice_name, '", "practiceAddress":"', physicians.practice_addr, '", "practiceCity":"', physicians.practice_city, '", "practiceState":"', physicians.practice_state, '", "practiceZip":"', physicians.practice_zip, '" }') SEPARATOR ','), ']'),'[ ]') AS assocPhys
    FROM patients 
    LEFT JOIN patient_physicians ON patient_physicians.id = patient_id 
    LEFT JOIN physicians         ON physicians.physician_id = patient_physicians.physician_id
    WHERE patient_id = _id
    GROUP BY patient_id;
END ;;
DELIMITER ;