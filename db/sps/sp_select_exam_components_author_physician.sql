DROP PROCEDURE IF EXISTS sp_select_exam_components_author_physician;
DELIMITER ;;
CREATE  PROCEDURE sp_select_exam_components_author_physician(IN physician_id INT(10) UNSIGNED) 
BEGIN 
    SELECT id,title,type,abbrev,description,sort,public, 
    IF(physician_id=author_physician,1,0) AS author_physician,
        IF((SELECT exam_components 
        FROM physician_prefs 
        WHERE FIND_IN_SET(abbrev,REPLACE(substr(exam_components,2,CHAR_LENGTH(exam_components)-2),'"','')) AND id=physician_id LIMIT 1
        ) IS NOT NULL,1,0) AS selected 
    FROM exam_components 
    WHERE public=1 OR (public=0 and author_physician=physician_id)
    ORDER BY sort; 
END ;;
DELIMITER ;
