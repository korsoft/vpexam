DROP TABLE IF EXISTS `stream_error`;
CREATE TABLE IF NOT EXISTS `stream_error` (
	`physician_id` 	INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`msg` 	        TEXT,
	`browser`       TEXT,
	`devices`       TEXT,
  	`created_at`    INT(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;