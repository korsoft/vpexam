DROP PROCEDURE IF EXISTS sp_insert_exam_document; 
DELIMITER ;; 
CREATE  PROCEDURE sp_insert_exam_document(IN _exam_id BIGINT(20), IN _name_document VARCHAR(255)) 
BEGIN 
	DECLARE _lastid VARCHAR(255);
	SET _lastid = (SELECT CONCAT( (max(id)+1),'.pdf') FROM patients_documents);
    INSERT INTO patients_documents(exam_id, name_document, filename, created_at) VALUES (_exam_id, _name_document, _lastid, UNIX_TIMESTAMP());
    SELECT _lastid AS file;
END ;;
DELIMITER ;