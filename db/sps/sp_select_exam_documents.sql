DROP PROCEDURE IF EXISTS sp_select_exam_documents;
DELIMITER ;;
CREATE  PROCEDURE sp_select_exam_documents(IN _exam_id BIGINT(20) UNSIGNED ) 
BEGIN 
    SELECT id, filename, name_document
    FROM patients_documents 
    INNER JOIN exams ON exams.exam_id = patients_documents.exam_id 
    WHERE deleted_at IS NULL AND exams.exam_id = _exam_id ;
END ;;
DELIMITER ;