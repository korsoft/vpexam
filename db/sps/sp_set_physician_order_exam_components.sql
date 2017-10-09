DROP PROCEDURE IF EXISTS sp_set_physician_order_exam_components;
DELIMITER ;;
CREATE  PROCEDURE sp_set_physician_order_exam_components(
	IN _physicianid INT(10) UNSIGNED, 
	IN _components TEXT) 
BEGIN 
	UPDATE virtual_physical_secure.physicians SET order_exam_components=_components WHERE physician_id=_physicianid;

END ;;
DELIMITER ;