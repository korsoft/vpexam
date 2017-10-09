DROP PROCEDURE IF EXISTS sp_select_exam_components_author_physician;
DELIMITER ;;
CREATE  PROCEDURE sp_select_exam_components_author_physician(IN _physician_id INT(10) UNSIGNED)
BEGIN
	SET SESSION group_concat_max_len = 1000000000;
	SET @query_ids =  CONCAT ('SELECT GROUP_CONCAT(order_exam_components) INTO @num_ids FROM physicians WHERE physician_id=',_physician_id);
	PREPARE stmt FROM @query_ids;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @query = CONCAT('SELECT id, title, type, abbrev, description, sort, public,
    IF(',_physician_id,' = author_physician, 1, 0) AS author_physician, 
    IF(created_at IS NOT NULL, 1, 0) AS selected,UNIX_TIMESTAMP(updated_at) AS updated_at 
    FROM exam_components 
    LEFT JOIN physicians_exam_components ON exam_component_abbrev = abbrev AND physician_id =', _physician_id, 
    ' WHERE deleted_at IS NULL
    ORDER BY FIELD(id,',@num_ids,')');
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
END ;;
DELIMITER ;
