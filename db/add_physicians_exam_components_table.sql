DROP TABLE IF EXISTS `physicians_exam_components`;
CREATE TABLE IF NOT EXISTS `physicians_exam_components` (
    `physician_id`          INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `exam_component_abbrev` VARCHAR(10)  NOT NULL DEFAULT '',
    `created_at`            INT(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE physicians_exam_components ADD PRIMARY KEY(`physician_id`, `exam_component_abbrev`);
