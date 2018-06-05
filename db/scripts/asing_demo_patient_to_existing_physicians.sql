/* GET DOCTORS HAS WRONG DEMO PATIENT */
SELECT GROUP_CONCAT(DISTINCT physician_id SEPARATOR '|'), GROUP_CONCAT(DISTINCT id SEPARATOR '|') INTO @physicians, @patients
FROM patient_physicians 
JOIN patients ON LOWER(first_name) = 'kristen' AND LOWER(last_name) = 'chriswell' AND patient_id <> 455
WHERE id = patient_id;

/* ADD DEMO PATIENT TO DOCTORS WHO HAS THE WRONG DEMO */
INSERT IGNORE INTO patient_physicians (
    SELECT DISTINCT 455, physician_id
    FROM patient_physicians 
    WHERE physician_id REGEXP CONCAT('^(', @physicians, ')$') AND id REGEXP CONCAT('^(', @patients, ')$')
);

/* REMOVE WRONG DEMO PATIENTS */
DELETE 
FROM patient_physicians
WHERE physician_id REGEXP CONCAT('^(', @physicians, ')$') AND id REGEXP CONCAT('^(', @patients, ')$');