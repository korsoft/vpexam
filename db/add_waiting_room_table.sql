CREATE TABLE `waiting_room` (
	`physician_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`patient_id`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `patient_name` VARCHAR(200)     NOT NULL DEFAULT '',
  `entered_at`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(`physician_id`,`patient_id`),
  INDEX `wr_idx_patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `waiting_room_history` (
	`physician_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`patient_id`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `patient_name` VARCHAR(200)     NOT NULL DEFAULT '',
  `entered_at`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `leaved_at`    INT(10) UNSIGNED NOT NULL DEFAULT 0,
  INDEX `wrh_idx_physician_id` (`physician_id`),
  INDEX `wrh_idx_patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;