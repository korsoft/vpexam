DROP PROCEDURE IF EXISTS sp_set_physician_exam_components;
DELIMITER ;;
CREATE DEFINER=vp_user@localhost PROCEDURE sp_set_physician_exam_components(
	IN _physicianid INT(10) UNSIGNED, 
	IN _components TEXT) 
BEGIN 
	DECLARE __date INT(10) UNSIGNED DEFAULT UNIX_TIMESTAMP();
	DELETE FROM virtual_physical_secure.physicians_exam_components WHERE physician_id = _physicianid;
	SET @query = CONCAT('INSERT IGNORE INTO virtual_physical_secure.physicians_exam_components(exam_component_abbrev, physician_id, created_at) VALUES(TRIM(', REPLACE(QUOTE(_components), ',', CONCAT("'), ", _physicianid, ", ", __date, "), (TRIM('")), '), ', _physicianid, ', ', __date, ')');

	SELECT @query;

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

END ;;
DELIMITER ;
