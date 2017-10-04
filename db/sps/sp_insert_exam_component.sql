DROP PROCEDURE IF EXISTS sp_insert_exam_component;
DELIMITER ;;
CREATE  PROCEDURE sp_insert_exam_component(IN _title VARCHAR(2048),IN _type ENUM('a','v'),IN _abbrev VARCHAR(50),IN _description VARCHAR(4096),
IN _sort TINYINT(3) unsigned,IN _public TINYINT(1) UNSIGNED,IN _author_physician INT(10) UNSIGNED) 
BEGIN 
    DECLARE idresult INT(6);
    DECLARE getCount INT(10);
        SET getCount = (
            SELECT MAX(id)
            FROM exam_components) + 1;
    IF NOT EXISTS (SELECT id FROM exam_components WHERE title=_title AND abbrev=_abbrev AND deleted_at IS NULL) THEN 
        INSERT INTO exam_components(title,type,abbrev,description,sort,public,author_physician) VALUES (_title,_type,_abbrev,_description,getCount,_public,_author_physician); 
        SET idresult = LAST_INSERT_ID();
    ELSE
        SET idresult = 0;
    END IF;
    SELECT idresult;
END ;;
DELIMITER ;
