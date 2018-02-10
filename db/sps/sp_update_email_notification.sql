DROP PROCEDURE IF EXISTS sp_update_email_notification;
DELIMITER ;;
CREATE  PROCEDURE sp_update_email_notification(IN _physician_id INT(10) UNSIGNED, IN _emailnotification INT(2)) 
BEGIN 
    UPDATE physicians SET email_notification=_emailnotification WHERE physician_id = _physician_id;
END ;;
DELIMITER ;
