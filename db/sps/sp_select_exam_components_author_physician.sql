DROP PROCEDURE IF EXISTS sp_select_exam_components_author_physician;
DELIMITER ;;
CREATE  PROCEDURE sp_select_exam_components_author_physician(IN _physician_id INT(10) UNSIGNED) 
BEGIN 
    SELECT id, title, type, abbrev, description, sort, public, 
    IF(_physician_id = author_physician, 1, 0) AS author_physician, 
    IF(created_at IS NOT NULL, 1, 0) AS selected 
    FROM exam_components 
    LEFT JOIN physicians_exam_components ON exam_component_abbrev = abbrev AND physician_id = _physician_id 
    ORDER BY sort;
END ;;
DELIMITER ;
