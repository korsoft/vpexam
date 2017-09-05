DROP PROCEDURE IF EXISTS dbcode.sp_store_stream_error;
DELIMITER ;;
CREATE PROCEDURE dbcode.sp_store_stream_error(
    IN _physicianid INT(10) UNSIGNED, 
    IN _msg TEXT,
    IN _browser TEXT,
    IN _devices TEXT
) 
BEGIN 
  INSERT INTO virtual_physical_secure.stream_error(physician_id, msg, browser, devices, created_at) VALUES(_physicianid, _msg, _browser, _devices, UNIX_TIMESTAMP());
END ;;
DELIMITER ;
