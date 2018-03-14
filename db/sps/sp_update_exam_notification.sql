DROP PROCEDURE IF EXISTS sp_update_exam_notification;
DELIMITER ;;
CREATE  PROCEDURE sp_update_exam_notification(IN _physician_id INT(10) UNSIGNED, IN _examnotification INT(2)) 
BEGIN 
    UPDATE physicians SET exam_notification=_examnotification WHERE physician_id = _physician_id;
END ;;
DELIMITER ;
