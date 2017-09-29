DROP PROCEDURE IF EXISTS sp_update_exam_component;
DELIMITER ;;
CREATE  PROCEDURE sp_update_exam_component(IN _id INT(6),IN _title VARCHAR(2048),IN _type ENUM('a','v'),IN _abbrev VARCHAR(50),IN _description VARCHAR(4096),
IN _sort TINYINT(3) unsigned,IN _public TINYINT(1) UNSIGNED,IN _author_physician INT(10) UNSIGNED) 
BEGIN 
    DECLARE idresult INT(6);
    DECLARE orsort INT(10);
    SET orsort = (
            SELECT sort
            FROM exam_components WHERE id=_id);

    IF EXISTS (SELECT id FROM exam_components WHERE id=_id) THEN 
        UPDATE exam_components
        SET title=_title,type=_type,abbrev=_abbrev,description=_description,sort=orsort,public=_public,author_physician=_author_physician
        WHERE id=_id; 
        SET idresult = _id;
    ELSE
        SET idresult = 0;
    END IF;
    SELECT idresult;
END ;;
DELIMITER ;
