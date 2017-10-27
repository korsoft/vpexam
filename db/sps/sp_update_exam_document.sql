DROP PROCEDURE IF EXISTS sp_update_exam_document;
DELIMITER ;;
CREATE  PROCEDURE sp_update_exam_document(IN _id INT(10) UNSIGNED, IN _filename3 VARCHAR(2048)) 
BEGIN 
    UPDATE patients_documents SET name_document=_filename3, updated_at=UNIX_TIMESTAMP() WHERE id = _id;
END ;;
DELIMITER ;