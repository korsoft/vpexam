DROP PROCEDURE IF EXISTS sp_delete_exam_document;
DELIMITER ;;
CREATE  PROCEDURE sp_delete_exam_document(IN _id INT(6) UNSIGNED) 
BEGIN 
        UPDATE patients_documents SET deleted_at=CURRENT_TIMESTAMP WHERE id=_id; 
END ;;
DELIMITER ;