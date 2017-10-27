DROP PROCEDURE IF EXISTS add_physicians_exam_components;

DELIMITER ;;

CREATE PROCEDURE add_physicians_exam_components()
BEGIN 
  DECLARE __date    	INT(10) UNSIGNED DEFAULT UNIX_TIMESTAMP(); 
  DECLARE __id          INT(10) UNSIGNED DEFAULT 0;
  DECLARE __components  VARCHAR(500)     DEFAULT '';
  DECLARE __flag		BOOLEAN      DEFAULT FALSE;
  DECLARE __cursor		CURSOR FOR SELECT id, exam_components FROM physician_prefs;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET __flag := TRUE;

  	OPEN __cursor;
	TABLES_LOOP: LOOP
		SET __id         = 0;
		SET __components = '';
		FETCH __cursor INTO __id, __components;
		IF __flag AND 0 = __id THEN 
			SET __flag := FALSE;
			CLOSE __cursor;
			LEAVE TABLES_LOOP;
		END IF;

		SET __components = REPLACE(REPLACE(REPLACE(__components, '"', ''), '[', ''), ']', '');

		SET @query = CONCAT('INSERT IGNORE 
							INTO physicians_exam_components(exam_component_abbrev, physician_id, created_at) 
							VALUES(TRIM(', REPLACE(QUOTE(__components), ',', CONCAT("'), ", __id, ", ", __date, "), (TRIM('")), '), ', __id, ', ', __date, ')');

	    PREPARE stmt FROM @query;
	    EXECUTE stmt;
	    DEALLOCATE PREPARE stmt;
	END LOOP TABLES_LOOP;
END ;;
DELIMITER ;
