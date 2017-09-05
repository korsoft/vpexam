USE virtual_physical_secure;
-- Borramos la llave foranea para que no cause problemas en el alert table de physicians
ALTER TABLE patient_physicians DROP FOREIGN KEY patient_physicians_ibfk_2;
-- Borramos la llave primaria del physicians
/*ALTER TABLE physicians DROP PRIMARY KEY;*/
-- Se crea una columna en la tabla de physicians para almacenar el id viejo --
ALTER TABLE physicians ADD COLUMN `old_physician_id` INT(11) NOT NULL;
-- Se agrega el valor de patient_id a la nueva columna agregada
UPDATE physicians SET old_physician_id = physician_id;
-- Cambiamos las columnas
ALTER TABLE physicians DROP       `physician_id`;
ALTER TABLE physicians ADD COLUMN `physician_id`            INT(10) UNSIGNED       NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE physicians MODIFY     `npi`                     VARCHAR(10)            NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `username`                VARCHAR(100)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `email`                   VARCHAR(150)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `password`                CHAR(128)              NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `salt`                    CHAR(128)              NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `first_name`              VARCHAR(150)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `middle_name`             VARCHAR(150)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `last_name`               VARCHAR(150)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `gender`                  ENUM('male', 'female') NOT NULL DEFAULT 'male';
ALTER TABLE physicians MODIFY     `dob`                     DATE                   NOT NULL DEFAULT '0000-00-00';
ALTER TABLE physicians MODIFY     `phone`                   VARCHAR(15)            NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `practice_name`           VARCHAR(300)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `practice_addr`           VARCHAR(300)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `practice_city`           VARCHAR(200)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `practice_state`          CHAR(2)                NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `practice_zip`            VARCHAR(9)             NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `home_addr`               VARCHAR(300)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `home_city`               VARCHAR(200)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `home_state`              CHAR(2)                NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `home_zip`                VARCHAR(9)             NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `recently_viewed`         VARCHAR(150)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_name`                VARCHAR(300)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_covered_entity`      VARCHAR(400)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_state`               CHAR(2)                NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_business_type`       CHAR(2)                NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_business_type_other` VARCHAR(200)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_org_type`            VARCHAR(200)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_addr`                VARCHAR(500)           NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_title`               VARCHAR(30)            NOT NULL DEFAULT '';
ALTER TABLE physicians MODIFY     `baa_needed`              TINYINT(1) UNSIGNED    NOT NULL DEFAULT 0;
ALTER TABLE physicians MODIFY     `assoc_hospital`          INT(8) UNSIGNED        NOT NULL DEFAULT 0;

-- Creamos los indices necesarios --
CREATE INDEX phy_email_idx      ON physicians (email)      USING BTREE;
CREATE INDEX phy_first_name_idx ON physicians (first_name) USING BTREE;
CREATE INDEX phy_last_name_idx  ON physicians (last_name)  USING BTREE;
-- Se pone el auto increment de la tabla en el total de usuarios
ALTER TABLE physicians AUTO_INCREMENT=14;
-- Se crea sp para recorrer la tabla con el nombre de las tablas donde se usa el physician_id
DROP PROCEDURE IF EXISTS virtual_physical_secure.sp_add_new_id_to_tables;
DELIMITER ;;
CREATE PROCEDURE virtual_physical_secure.sp_add_new_id_to_tables() 
BEGIN 
    DECLARE __name VARCHAR(100) DEFAULT '';
    DECLARE __flag BOOLEAN DEFAULT FALSE;
      DECLARE __tables CURSOR FOR SELECT name FROM virtual_physical_secure.tables_with_physician_id;
      DECLARE CONTINUE HANDLER FOR NOT FOUND SET __flag := TRUE;
      -- Borramos la tabla temporal --
      DROP TABLE IF EXISTS tables_with_physician_id;
      -- Creamos una tabla temporal para almacenar el nombre de las tablas donde se usa el physician_id
    CREATE TEMPORARY TABLE tables_with_physician_id(
        name VARCHAR(100) NOT NULL DEFAULT ''
    );
    -- Insertamos en la tabla temporal el nombre de las tablas donde se encuentre el physician_id --
    INSERT INTO tables_with_physician_id 
    SELECT DISTINCT TABLE_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME = 'physician_id' AND TABLE_SCHEMA = 'virtual_physical_secure' AND TABLE_NAME <> 'physicians';
    -- Recorremos lo obtenido en el cursor
      OPEN __tables;
    tables_loop: LOOP
      SET __name = '';
      FETCH __tables INTO __name;
      IF __flag AND '' = __name THEN 
        SET __flag := FALSE;
        CLOSE __tables;
        LEAVE tables_loop;
      END IF;
      -- Creamos el query a ejecutar --
      SET @query = CONCAT('
        UPDATE ', __name, ' 
        JOIN physicians ON old_physician_id = ', __name , '.physician_id 
        SET ', __name, '.physician_id = physicians.physician_id;' 
      );
      -- Ejecutamos el query --
      PREPARE stmt FROM @query;
      EXECUTE stmt;
      DEALLOCATE PREPARE stmt;
  END LOOP tables_loop;
END ;;
DELIMITER ;
-- Llamamos al sp
CALL virtual_physical_secure.sp_add_new_id_to_tables();
-- Actualizamos tmb la tabla de patient_physicians con los nuevos id auto generados
UPDATE patient_physicians
JOIN physicians ON old_physician_id = patient_physicians.physician_id 
SET patient_physicians.physician_id = physicians.physician_id;
-- Se iguala el tipo de campo de patient_id en la tabla patient_physicians para poder agregar la llave foranea --
ALTER TABLE patient_physicians MODIFY `physician_id` INT(10) UNSIGNED NOT NULL DEFAULT 0;
-- Creamos de nuevo la llave foranea
ALTER TABLE patient_physicians ADD CONSTRAINT patient_physicians_ibfk_2 FOREIGN KEY (physician_id) REFERENCES physicians(physician_id);
