DROP PROCEDURE IF EXISTS dbcode.sp_add_patient_video_call;
DELIMITER ;;
CREATE DEFINER=vp_user@localhost PROCEDURE dbcode.sp_add_patient_video_call(
	OUT _id INT(10) UNSIGNED,
	IN _physicianid INT(10) UNSIGNED, 
	IN _patientid INT(10) UNSIGNED) 
BEGIN 
  INSERT INTO virtual_physical_secure.patient_physician_calls(physician_id, patient_id, created_at) VALUES(_physicianid, _patientid, UNIX_TIMESTAMP());
  SET _id = LAST_INSERT_ID();
END ;;
DELIMITER ;
