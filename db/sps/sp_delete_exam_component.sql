DROP PROCEDURE IF EXISTS sp_delete_exam_component;
DELIMITER ;;
CREATE  PROCEDURE sp_delete_exam_component(IN _id INT(6)) 
BEGIN 
    DECLARE idresult INT(6) DEFAULT 0;
    DECLARE __abbrev VARCHAR(50) DEFAULT '';
    BEGIN 
        IF EXISTS (SELECT abbrev  FROM exam_components WHERE id=_id AND deleted_at IS NULL) THEN 
            /*Get abbrev to delete component from physicians_exam_components table*/
            SELECT abbrev INTO __abbrev FROM exam_components WHERE id=_id AND deleted_at IS NULL;
            /*Update field */
            UPDATE exam_components SET deleted_at=CURRENT_TIMESTAMP WHERE id=_id; 
            SET idresult = _id;
        ELSE
            SET idresult = 0;
        END IF;
    END;
    BEGIN
        IF 0<idresult THEN
            DELETE FROM physicians_exam_components WHERE exam_component_abbrev = __abbrev;
        END IF;
    END;
    SELECT idresult;
END ;;
DELIMITER ;
