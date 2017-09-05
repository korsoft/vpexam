DROP TABLE IF EXISTS `patient_physician_calls`;
CREATE TABLE IF NOT EXISTS `patient_physician_calls` (
	`id`            INT(10) 	UNSIGNED 	NOT NULL PRIMARY kEY AUTO_INCREMENT,
	`physician_id` 	INT(10) 	UNSIGNED 	NOT NULL DEFAULT 0,
	`patient_id` 	INT(10) 	UNSIGNED 	NOT NULL DEFAULT 0,
  	`status`        TINYINT(1) 	UNSIGNED 	NOT NULL DEFAULT 1,
  	`created_at`    INT(10) 	UNSIGNED 	NOT NULL DEFAULT 0,
  	`deleted_at`    INT(10) 	UNSIGNED 	NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;